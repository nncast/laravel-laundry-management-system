<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaundryAppTest extends TestCase
{
    use RefreshDatabase;

    private Staff $admin;
    private Staff $cashier;
    private Customer $customer;
    private Service $wash;
    private Service $dry;
    private Addon $softener;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create(['business_name' => 'Test Laundry', 'address' => 'Somewhere', 'contact' => '09123456789']);

        $this->admin = Staff::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true,
        ]);
        $this->cashier = Staff::create([
            'name' => 'Cashier', 'username' => 'cashier', 'password' => 'secret123', 'role' => 'cashier', 'is_active' => true,
        ]);

        $type = ServiceType::create(['name' => 'Laundry', 'is_active' => true]);
        $this->wash = Service::create(['name' => 'Wash', 'service_type_id' => $type->id, 'price' => 100, 'is_active' => true]);
        $this->dry = Service::create(['name' => 'Dry', 'service_type_id' => $type->id, 'price' => 50, 'is_active' => true]);
        $this->softener = Addon::create(['name' => 'Softener', 'price' => 20, 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Juan', 'contact' => '09170000000']);
    }

    private function actingAsStaff(Staff $staff): static
    {
        return $this->withSession(['staff' => ['id' => $staff->id, 'name' => $staff->name, 'role' => $staff->role]]);
    }

    private function createOrder(array $overrides = []): Order
    {
        $response = $this->actingAsStaff($this->admin)->postJson('/pos/orders', array_merge([
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'discount' => 10,
            'items' => [['service_id' => $this->wash->id, 'qty' => 2]],
            'addons' => [['addon_id' => $this->softener->id]],
            'payment_amount' => 0,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        return Order::findOrFail($response->json('order.id'));
    }

    // ---------------------------------------------------------------- auth

    public function test_login_and_logout(): void
    {
        $this->post('/login', ['username' => 'admin', 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));
        $this->assertSame($this->admin->id, session('staff.id'));

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertNull(session('staff.id'));
    }

    public function test_deactivated_staff_is_logged_out(): void
    {
        $this->cashier->update(['is_active' => false]);

        $this->actingAsStaff($this->cashier)->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_open_admin_pages(): void
    {
        foreach (['/staff/admin', '/settings/mastersettings', '/backup/download', '/reports/daily', '/services/list'] as $url) {
            $this->actingAsStaff($this->cashier)->get($url)->assertRedirect(route('dashboard'));
        }
    }

    // ---------------------------------------------------------------- pages

    public function test_every_page_renders_for_admin(): void
    {
        $order = $this->createOrder();

        $pages = [
            '/dashboard', '/orders', '/pos', "/pos/{$order->id}/edit", "/orders/{$order->id}/details",
            '/customers', '/inventory/products', '/inventory/categories', '/inventory/units',
            '/services/list', '/services/type', '/services/addons', '/staff/admin',
            '/reports/daily', '/reports/order', '/reports/sales', '/settings/mastersettings',
        ];

        foreach ($pages as $url) {
            $this->actingAsStaff($this->admin)->get($url)->assertOk();
        }
    }

    public function test_dashboard_json_endpoints_return_numbers(): void
    {
        $this->createOrder(['payment_amount' => 500]);

        $stats = $this->actingAsStaff($this->admin)->getJson('/dashboard/stats')->assertOk()->json('stats');
        $this->assertSame(1, $stats['totalOrders']);
        $this->assertIsNumeric($stats['totalRevenue']);

        foreach (['week' => 7, 'month' => null, 'year' => 12] as $period => $count) {
            $data = $this->actingAsStaff($this->admin)->getJson("/dashboard/chart-data/{$period}")->assertOk()->json('data');
            $this->assertCount(count($data['labels']), $data['sales']);
            if ($count) {
                $this->assertCount($count, $data['labels']);
            }
        }

        // The month chart must cover days 29-31 as well
        $month = $this->actingAsStaff($this->admin)->getJson('/dashboard/chart-data/month')->json('data.labels');
        $this->assertContains('Week ' . (intdiv(now()->endOfMonth()->day - 1, 7) + 1), $month);
    }

    // ---------------------------------------------------------------- POS

    public function test_pos_create_computes_totals_and_caps_payment_at_total(): void
    {
        // 2 x 100 + 20 addon - 10 discount = 210; customer hands over 500
        $order = $this->createOrder(['payment_amount' => 500]);

        $this->assertEquals(200, $order->subtotal);
        $this->assertEquals(210, $order->total);
        $this->assertEquals(210, $order->paid_amount);
        $this->assertEquals(0, $order->balance);
        $this->assertSame('processing', $order->status);
        $this->assertEquals(210, $order->payments()->sum('amount'));
        $this->assertSame('cash', $order->payments()->first()->payment_method);
    }

    public function test_discount_is_never_more_than_the_order(): void
    {
        // 2 x 100 + 20 addon = 220, so a 1000 discount only takes off 220
        $order = $this->createOrder(['discount' => 1000]);

        $this->assertEquals(220, $order->discount);
        $this->assertEquals(0, $order->total);
    }

    public function test_percent_discount_is_worked_out_on_the_server(): void
    {
        // 2 x 100 + 20 addon = 220; 10% off = 22
        $order = $this->createOrder(['discount' => 10, 'discount_type' => 'percent']);

        $this->assertEquals(10, $order->discount_percent);
        $this->assertEquals(22, $order->discount);
        $this->assertEquals(198, $order->total);

        $this->actingAsStaff($this->admin)->get("/orders/{$order->id}/details")
            ->assertOk()->assertSee('Discount (10%)');
    }

    public function test_percent_discount_over_100_is_rejected(): void
    {
        $this->actingAsStaff($this->admin)->postJson('/pos/orders', [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'discount' => 150,
            'discount_type' => 'percent',
            'items' => [['service_id' => $this->wash->id, 'qty' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('discount');
    }

    public function test_editing_an_order_keeps_its_percent_discount(): void
    {
        $order = $this->createOrder(['discount' => 10, 'discount_type' => 'percent']);
        $edit = fn (array $changes) => $this->actingAsStaff($this->admin)->putJson("/pos/{$order->id}", array_merge([
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'items' => [['service_id' => $this->wash->id, 'qty' => 3]],
            'addons' => [['addon_id' => $this->softener->id]],
            'payment_amount' => 0,
        ], $changes))->assertOk();

        // 3 x 100 + 20 = 320, still 10% off
        $edit(['discount' => 10, 'discount_type' => 'percent']);
        $order->refresh();
        $this->assertEquals(32, $order->discount);
        $this->assertEquals(288, $order->total);
        $this->actingAsStaff($this->admin)->get("/pos/{$order->id}/edit")
            ->assertOk()->assertSee('name="discountType" value="percent" checked', false);

        // Switching back to a peso amount drops the percentage
        $edit(['discount' => 15, 'discount_type' => 'amount']);
        $order->refresh();
        $this->assertNull($order->discount_percent);
        $this->assertEquals(15, $order->discount);
        $this->assertEquals(305, $order->total);
    }

    public function test_pos_customer_summary_offers_the_last_order_again(): void
    {
        $this->createOrder();   // Wash x2 + Softener, 210 owed
        $this->wash->update(['is_active' => false]);

        $this->actingAsStaff($this->cashier)->getJson("/pos/customers/{$this->customer->id}/summary")
            ->assertOk()
            ->assertJsonPath('orders_count', 1)
            ->assertJsonPath('balance_due', 210)
            ->assertJsonPath('last_order.items.0.service_id', $this->wash->id)
            ->assertJsonPath('last_order.items.0.qty', 2)
            ->assertJsonPath('last_order.items.0.available', false)
            ->assertJsonPath('last_order.addon_ids', [$this->softener->id]);

        // "New order" on the customers page opens the POS with the customer picked
        $this->actingAsStaff($this->cashier)->get("/pos?customer={$this->customer->id}")
            ->assertOk()->assertViewHas('preselectedCustomerId', $this->customer->id);
    }

    public function test_pos_ignores_prices_sent_by_the_browser(): void
    {
        $order = $this->createOrder([
            'items' => [['service_id' => $this->wash->id, 'qty' => 1, 'price' => 1]],
            'addons' => [],
            'discount' => 0,
        ]);

        $this->assertEquals(100, $order->total);
    }

    public function test_pos_groups_active_services_by_type(): void
    {
        $iron = ServiceType::create(['name' => 'Ironing', 'is_active' => true]);
        Service::create(['name' => 'Press', 'service_type_id' => $iron->id, 'price' => 20, 'is_active' => true]);
        Service::create(['name' => 'Retired', 'service_type_id' => $iron->id, 'price' => 5, 'is_active' => false]);

        $response = $this->actingAsStaff($this->admin)->get('/pos')->assertOk();

        $groups = $response->viewData('serviceGroups');
        $this->assertSame(['Ironing', 'Laundry'], $groups->keys()->all());
        $this->assertSame(['Press'], $groups['Ironing']->pluck('name')->all());
        $this->assertSame(['Dry', 'Wash'], $groups['Laundry']->pluck('name')->all());
        $response->assertSeeInOrder(['Ironing', 'Press', 'Laundry', 'Dry', 'Wash'])->assertDontSee('Retired');
    }

    public function test_pos_merges_duplicate_service_lines(): void
    {
        $order = $this->createOrder([
            'items' => [
                ['service_id' => $this->wash->id, 'qty' => 1],
                ['service_id' => $this->wash->id, 'qty' => 2],
            ],
            'addons' => [],
            'discount' => 0,
        ]);

        $this->assertSame(1, $order->items()->count());
        $this->assertEquals(300, $order->total);
    }

    public function test_pos_edit_recalculates_totals_and_keeps_existing_payments(): void
    {
        $order = $this->createOrder(['payment_amount' => 100]);
        $this->assertEquals(100, $order->paid_amount);

        // Change wash qty to 1, add dry, drop the addon, no new payment
        $this->actingAsStaff($this->admin)->putJson("/pos/{$order->id}", [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [
                ['service_id' => $this->wash->id, 'qty' => 1],
                ['service_id' => $this->dry->id, 'qty' => 1],
            ],
            'addons' => [],
            'payment_amount' => 0,
        ])->assertOk()->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(150, $order->subtotal);
        $this->assertEquals(150, $order->total);
        $this->assertEquals(100, $order->paid_amount, 'Editing must not wipe earlier payments');
        $this->assertEquals(50, $order->balance);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame(0, $order->addons()->count());
    }

    public function test_pos_edit_keeps_original_price_when_service_price_changes(): void
    {
        $order = $this->createOrder(['addons' => [], 'discount' => 0]);
        $this->wash->update(['price' => 999]);

        $this->actingAsStaff($this->admin)->putJson("/pos/{$order->id}", [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [['service_id' => $this->wash->id, 'qty' => 3]],
            'addons' => [],
        ])->assertOk();

        $this->assertEquals(300, $order->fresh()->total);
    }

    public function test_pos_validation_errors_are_json(): void
    {
        $this->actingAsStaff($this->admin)->postJson('/pos/orders', ['items' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id', 'items']);
    }

    public function test_completed_order_cannot_be_edited(): void
    {
        $order = $this->createOrder();
        $order->update(['status' => 'completed']);

        $this->actingAsStaff($this->admin)->get("/pos/{$order->id}/edit")
            ->assertRedirect(route('orders.details', $order));
    }

    // ---------------------------------------------------------------- order details

    public function test_add_payment_without_method_and_change_is_not_recorded(): void
    {
        $order = $this->createOrder(); // total 210, unpaid

        $this->actingAsStaff($this->admin)->postJson("/orders/{$order->id}/add-payment", ['amount' => 300])
            ->assertOk()
            ->assertJson(['success' => true, 'change' => 90]);

        $order->refresh();
        $this->assertEquals(210, $order->paid_amount);

        $this->actingAsStaff($this->admin)->postJson("/orders/{$order->id}/add-payment", ['amount' => 10])
            ->assertStatus(422);
    }

    public function test_add_notes_and_update_status(): void
    {
        $order = $this->createOrder();

        $this->actingAsStaff($this->admin)->postJson("/orders/{$order->id}/add-notes", ['notes' => 'Handle with care'])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Handle with care', $order->fresh()->notes);

        $this->actingAsStaff($this->admin)->postJson("/orders/{$order->id}/update-status", ['status' => 'completed'])
            ->assertOk()->assertJson(['order' => ['status' => 'completed']]);
    }

    public function test_ready_orders_wait_for_pickup_and_can_still_be_edited(): void
    {
        $order = $this->createOrder();

        $this->actingAsStaff($this->cashier)->postJson("/orders/{$order->id}/update-status", ['status' => 'ready'])
            ->assertOk()->assertJson(['order' => ['status' => 'ready', 'status_label' => 'Ready for pickup']]);

        $order->refresh();
        $this->assertTrue($order->can_edit);
        $this->actingAsStaff($this->admin)->get("/pos/{$order->id}/edit")->assertOk();

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $this->actingAsStaff($this->admin)->get("/reports/order?start_date={$start}&end_date={$end}&status=ready")
            ->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['total_orders'] === 1 && $s['ready_count'] === 1);
    }

    public function test_orders_list_filters_by_tab_payment_and_date(): void
    {
        $paid = $this->createOrder(['payment_amount' => 1000]);   // fully paid, so it moves to processing
        $unpaid = $this->createOrder();
        $old = $this->createOrder(['order_date' => now()->subDays(5)->toDateString()]);   // pending 5 days

        $this->actingAsStaff($this->cashier)->get('/orders?status=overdue')
            ->assertOk()
            ->assertViewHas('orders', fn ($page) => $page->pluck('id')->all() === [$old->id])
            ->assertViewHas('counts', fn ($c) => $c[''] === 3 && $c['open'] === 3 && $c['overdue'] === 1 && $c['processing'] === 1);

        $this->actingAsStaff($this->cashier)->get('/orders?payment=unpaid')
            ->assertViewHas('orders', fn ($page) => $page->pluck('id')->sort()->values()->all() === [$unpaid->id, $old->id]);

        $this->actingAsStaff($this->cashier)->get('/orders?payment=paid')
            ->assertViewHas('orders', fn ($page) => $page->pluck('id')->all() === [$paid->id]);

        $this->actingAsStaff($this->cashier)->get('/orders?from=' . now()->toDateString())
            ->assertViewHas('orders', fn ($page) => $page->count() === 2);
    }

    public function test_bulk_status_moves_several_orders(): void
    {
        $first = $this->createOrder();
        $second = $this->createOrder();

        $this->actingAsStaff($this->cashier)->postJson('/orders/bulk-status', ['ids' => [$first->id, $second->id], 'status' => 'ready'])
            ->assertOk()->assertJson(['updated' => 2]);
        $this->assertSame(['ready', 'ready'], Order::whereIn('id', [$first->id, $second->id])->pluck('status')->all());

        $this->actingAsStaff($this->cashier)->postJson('/orders/bulk-status', ['ids' => [$first->id], 'status' => 'lost'])
            ->assertStatus(422);
    }

    public function test_dashboard_counts_ready_overdue_and_unpaid_orders(): void
    {
        $ready = $this->createOrder();
        $ready->update(['status' => 'ready']);
        $this->createOrder(['order_date' => now()->subDays(2)->toDateString()]);   // pending 2 days: overdue

        $stats = $this->actingAsStaff($this->admin)->getJson('/dashboard/stats')->json('stats');
        $this->assertSame(1, $stats['readyCount']);
        $this->assertSame(1, $stats['overdueCount']);
        $this->assertSame(2, $stats['unpaidCount']);
        $this->assertEquals(420, $stats['unpaidBalance']);

        $this->actingAsStaff($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('readyOrders', fn ($orders) => $orders->pluck('id')->all() === [$ready->id]);
    }

    public function test_customer_list_shows_order_count_and_balance(): void
    {
        $this->createOrder();   // 210 owed by Juan

        $this->actingAsStaff($this->cashier)->get('/customers')
            ->assertOk()
            ->assertViewHas('customers', fn ($page) => $page->first()->orders_count === 1 && (float) $page->first()->balance_due === 210.0);
    }

    public function test_cashier_cannot_delete_orders(): void
    {
        $order = $this->createOrder();

        $this->actingAsStaff($this->cashier)->delete("/orders/{$order->id}")->assertRedirect(route('dashboard'));
        $this->assertNotNull($order->fresh());
    }

    // ---------------------------------------------------------------- data protection

    public function test_services_and_addons_used_in_orders_cannot_be_deleted(): void
    {
        $this->createOrder();

        $this->actingAsStaff($this->admin)->deleteJson("/services/list/{$this->wash->id}")->assertStatus(422);
        $this->actingAsStaff($this->admin)->deleteJson("/services/addons/{$this->softener->id}")->assertStatus(422);
        $this->assertSame(1, DB::table('order_items')->count());
        $this->assertSame(1, DB::table('order_addons')->count());

        // An unused service can still be deleted
        $this->actingAsStaff($this->admin)->deleteJson("/services/list/{$this->dry->id}")->assertOk();
    }

    public function test_service_can_be_deactivated(): void
    {
        $this->actingAsStaff($this->admin)->putJson("/services/list/{$this->dry->id}", [
            'name' => 'Dry', 'service_type_id' => $this->dry->service_type_id, 'price' => 50, 'is_active' => '0',
        ])->assertOk();

        $this->assertFalse((bool) $this->dry->fresh()->is_active);
    }

    public function test_staff_with_orders_cannot_be_deleted_and_admin_cannot_lock_self_out(): void
    {
        $this->createOrder();

        $this->actingAsStaff($this->admin)->delete("/staff/{$this->admin->id}")->assertSessionHas('error');
        $this->assertNotNull($this->admin->fresh());

        $this->actingAsStaff($this->admin)->put("/staff/{$this->admin->id}", [
            'name' => 'Admin', 'username' => 'admin', 'role' => 'cashier', 'is_active' => 1,
        ])->assertSessionHas('error');
        $this->assertSame('admin', $this->admin->fresh()->role);

        $this->actingAsStaff($this->admin)->delete("/staff/{$this->cashier->id}")->assertSessionHas('success');
    }

    // ---------------------------------------------------------------- customers

    public function test_customer_can_be_added_from_pos_as_json(): void
    {
        $this->actingAsStaff($this->cashier)->postJson('/customers', ['name' => 'Maria', 'contact' => '09181234567'])
            ->assertCreated()
            ->assertJsonPath('customer.name', 'Maria');

        $this->actingAsStaff($this->cashier)->postJson('/customers', ['name' => 'Bad', 'contact' => '123'])
            ->assertStatus(422)->assertJsonValidationErrors('contact');
    }

    public function test_customer_list_is_paginated_and_searchable(): void
    {
        foreach (range(1, 20) as $i) {
            Customer::create(['name' => "Customer {$i}"]);
        }

        $this->actingAsStaff($this->admin)->get('/customers?page=2')->assertOk()->assertSee('Showing 16');
        $this->actingAsStaff($this->admin)->get('/customers?search=Juan')->assertOk()->assertSee('Juan')->assertDontSee('Customer 7');
    }

    // ---------------------------------------------------------------- settings

    public function test_settings_save(): void
    {
        $this->actingAsStaff($this->admin)->post('/settings/system', [
            'business_name' => 'Sparkle Laundry',
            'address' => 'Main St',
            'contact' => '09998887777',
        ])->assertSessionHas('success');

        $this->assertSame('Sparkle Laundry', SystemSetting::first()->business_name);

        $this->actingAsStaff($this->admin)->post('/settings/system', ['business_name' => ''])
            ->assertSessionHasErrors('business_name');
    }

    // ---------------------------------------------------------------- reports

    public function test_reports_include_the_last_day_and_export_csv(): void
    {
        $lastDay = now()->endOfMonth()->toDateString();
        $this->createOrder(['order_date' => $lastDay, 'payment_amount' => 1000]);

        $start = now()->startOfMonth()->toDateString();

        $this->actingAsStaff($this->admin)->get("/reports/order?start_date={$start}&end_date={$lastDay}")
            ->assertOk()->assertViewHas('summary', fn ($s) => $s['total_orders'] === 1);

        $this->actingAsStaff($this->admin)->get("/reports/sales?start_date={$start}&end_date={$lastDay}")
            ->assertOk()->assertViewHas('summary', fn ($s) => $s['total_orders'] === 1 && $s['total_sales'] == 210);

        $csv = $this->actingAsStaff($this->admin)->get("/reports/order/download?start_date={$start}&end_date={$lastDay}");
        $csv->assertOk();
        $this->assertStringContainsString('Juan', $csv->streamedContent());

        $this->actingAsStaff($this->admin)->getJson("/reports/daily/download?date={$lastDay}")
            ->assertOk()->assertJsonPath('data.total_orders', 1);
    }
}
