<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\SystemSetting;
use App\Support\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Currency and formats: Settings → Currency & formats changes every amount, an
 * order keeps the currency it was taken in, and totals never add two currencies.
 */
class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    private Staff $admin;
    private Customer $customer;
    private Service $wash;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create(['business_name' => 'Test Laundry']);
        $this->admin = Staff::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true]);
        $type = ServiceType::create(['name' => 'Laundry', 'is_active' => true]);
        $this->wash = Service::create(['name' => 'Wash', 'service_type_id' => $type->id, 'price' => 1255, 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Juan']);
    }

    private function asAdmin(): static
    {
        return $this->withSession(['staff' => ['id' => $this->admin->id, 'name' => 'Admin', 'role' => 'admin']]);
    }

    /** Settings change mid-test: forget the per-request copies. */
    private function useSettings(array $values): void
    {
        SystemSetting::first()->update($values);
        $this->app->forgetInstance('system.settings');
        $this->app->forgetInstance(Format::class);
    }

    private function order(array $overrides = []): Order
    {
        $response = $this->asAdmin()->postJson('/pos/orders', $overrides + [
            'customer_id' => $this->customer->id,
            'order_date' => now()->toDateString(),
            'items' => [['service_id' => $this->wash->id, 'qty' => 1]],
            'payment_amount' => 0,
        ])->assertOk();

        return Order::findOrFail($response->json('order.id'));
    }

    public function test_amounts_follow_the_currency_and_number_format(): void
    {
        $this->assertSame('₱1,250.00', money(1250));

        $cases = [
            ['USD', 'comma_dot', 1250, '$1,250.00'],
            ['EUR', 'dot_comma', 1250, '€1.250,00'],
            ['JPY', 'comma_dot', 1250.4, '¥1,250'],
            ['GBP', 'comma_dot', 1250, '£1,250.00'],
            ['IDR', 'dot_comma', 1250000, 'Rp1.250.000'],
            ['MYR', 'space_comma', 1250.5, "RM1\u{202F}250,50"],
        ];
        foreach ($cases as [$currency, $numbers, $amount, $expected]) {
            $this->useSettings(['currency' => $currency, 'number_format' => $numbers]);
            $this->assertSame($expected, money($amount), "$currency $numbers");
        }

        // An amount can always be shown in its own currency
        $this->assertSame('₱99,50', money(99.5, 'PHP'));
        $this->assertSame('1', money_step('IDR'));
        $this->assertSame('0.01', money_step('PHP'));
    }

    public function test_orders_keep_their_currency_and_totals_never_mix_currencies(): void
    {
        $peso = $this->order();
        $this->assertSame('PHP', $peso->currency);

        $this->useSettings(['currency' => 'USD']);
        $dollar = $this->order();
        $this->assertSame('USD', $dollar->currency);

        // The old order still shows pesos and can't be re-priced in dollars
        $peso->refresh();
        $this->assertFalse($peso->can_edit);
        $this->asAdmin()->get("/orders/{$peso->id}/details")->assertOk()->assertSee('₱1,255.00');
        $this->asAdmin()->get("/pos/{$peso->id}/edit")->assertRedirect(route('orders.details', $peso))->assertSessionHas('error');
        $this->asAdmin()->get("/orders/{$dollar->id}/details")->assertOk()->assertSee('$1,255.00');

        // Reports add up dollars only and mention the pesos separately
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        $this->asAdmin()->get("/reports/sales?start_date={$start}&end_date={$end}")
            ->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['total_orders'] === 1 && $s['total_sales'] == 1255)
            ->assertViewHas('otherCurrencies', fn ($rows) => $rows->count() === 1 && $rows[0]->currency === 'PHP')
            ->assertSee('from before the currency changed');

        $stats = $this->asAdmin()->getJson('/dashboard/stats')->json('stats');
        $this->assertEquals(1255, $stats['unpaidBalance']);
        $this->assertSame(1, $stats['unpaidCount']);

        $csv = $this->asAdmin()->get("/reports/order/download?start_date={$start}&end_date={$end}")->streamedContent();
        $this->assertStringContainsString('Currency', $csv);
        $this->assertStringContainsString('Total amount (USD)', $csv);
    }

    public function test_yen_orders_round_to_whole_yen(): void
    {
        $this->useSettings(['currency' => 'JPY']);

        // 1,255 with 10% off = 125.5 off, rounded to 126 yen
        $order = $this->order(['discount' => 10, 'discount_type' => 'percent']);

        $this->assertEquals(126, $order->discount);
        $this->assertEquals(1129, $order->total);
        $this->asAdmin()->get("/orders/{$order->id}/details")->assertOk()->assertSee('¥1,129');
    }

    public function test_currency_and_formats_are_chosen_in_settings(): void
    {
        $this->asAdmin()->get('/settings/mastersettings')->assertOk()->assertSee('Currency &amp; formats', false);

        $this->asAdmin()->post('/settings/region', ['currency' => 'XXX', 'number_format' => 'comma_dot', 'date_format' => 'm/d/Y', 'time_format' => '12h'])
            ->assertSessionHasErrors('currency');

        $this->asAdmin()->post('/settings/region', ['currency' => 'EUR', 'number_format' => 'dot_comma', 'date_format' => 'd/m/Y', 'time_format' => '24h'])
            ->assertRedirect()
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Prices were not converted'));

        $settings = SystemSetting::first();
        $this->assertSame(['EUR', 'dot_comma', 'd/m/Y', '24h'], [$settings->currency, $settings->number_format, $settings->date_format, $settings->time_format]);

        $this->app->forgetInstance('system.settings');
        $this->app->forgetInstance(Format::class);
        $this->assertSame('03/11/2026 21:05', fmt_datetime('2026-11-03 21:05:00'));
    }
}
