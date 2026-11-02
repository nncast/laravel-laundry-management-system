<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\SystemSetting;
use App\Support\Barcode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PrintDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private Staff $admin;
    private Staff $cashier;
    private Customer $customer;
    private Service $wash;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create(['business_name' => 'Test Laundry', 'receipt_thank_you' => 'Salamat po!']);
        $this->admin = Staff::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true]);
        $this->cashier = Staff::create(['name' => 'Cashier', 'username' => 'cashier', 'password' => 'secret123', 'role' => 'cashier', 'is_active' => true]);
        $type = ServiceType::create(['name' => 'Laundry', 'is_active' => true]);
        $this->wash = Service::create(['name' => 'Wash', 'service_type_id' => $type->id, 'price' => 100, 'is_active' => true]);
        $this->customer = Customer::create(['name' => 'Juan', 'contact' => '09170000000']);
    }

    private function as(Staff $staff): static
    {
        return $this->withSession(['staff' => ['id' => $staff->id, 'name' => $staff->name, 'role' => $staff->role]]);
    }

    private function createOrder(array $overrides = []): Order
    {
        $response = $this->as($this->admin)->postJson('/pos/orders', array_merge([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-11-03',
            'ready_by' => '2026-11-05',
            'weight' => 6.5,
            'items' => [['service_id' => $this->wash->id, 'qty' => 2]],
            'payment_amount' => 50,
        ], $overrides));

        $response->assertOk()->assertJson(['success' => true]);

        return Order::findOrFail($response->json('order.id'));
    }

    public function test_every_document_prints_on_every_paper(): void
    {
        $order = $this->createOrder();

        foreach (['receipt' => 'Receipt', 'ticket' => 'Claim ticket', 'job' => 'Job order'] as $document => $title) {
            foreach (['58mm', '80mm', 'a4', 'letter'] as $paper) {
                $this->as($this->cashier)->get("/orders/{$order->id}/print/{$document}?paper={$paper}")
                    ->assertOk()
                    ->assertSee($title)
                    ->assertSee($order->order_number)
                    ->assertSee('Juan')
                    ->assertSee('Nov 5, 2026')
                    ->assertSee('6.5 kg')
                    ->assertSee("paper-{$paper}", false);
            }
        }

        $this->as($this->cashier)->get("/orders/{$order->id}/print/receipt")
            ->assertSee('R-' . str_pad((string) $order->payments()->value('id'), 6, '0', STR_PAD_LEFT))
            ->assertSee('Salamat po!')
            ->assertSee('Balance due');

        // Only these three documents (anything else falls through to the app's catch-all redirect)
        $this->as($this->cashier)->get("/orders/{$order->id}/print/invoice")->assertRedirect(route('login'));
    }

    public function test_pdfs_download_and_a_roll_is_as_long_as_its_receipt(): void
    {
        $order = $this->createOrder();

        foreach (['80mm', 'a4'] as $paper) {
            $response = $this->as($this->cashier)->get("/orders/{$order->id}/print/receipt/pdf?paper={$paper}")
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertDownload("{$order->order_number}-receipt.pdf");
            $pdf = $response->getContent();
            $this->assertStringStartsWith('%PDF-', $pdf);

            // MediaBox: 80 mm = 226.77 pt wide and only as long as the receipt; A4 = 595.28 x 841.89 pt
            preg_match('~/MediaBox \[0\.0+ 0\.0+ ([\d.]+) ([\d.]+)\]~', $pdf, $box);
            if ($paper === '80mm') {
                $this->assertEqualsWithDelta(226.77, (float) $box[1], 0.1);
                $this->assertGreaterThan(300, (float) $box[2]);
                $this->assertLessThan(1200, (float) $box[2]);
            } else {
                $this->assertEqualsWithDelta(595.28, (float) $box[1], 0.1);
            }
        }
    }

    public function test_a_receipt_is_for_one_payment(): void
    {
        $order = $this->createOrder();   // total 200, 50 paid at the POS
        $this->as($this->cashier)->postJson("/orders/{$order->id}/add-payment", ['amount' => 30, 'payment_method' => 'card'])->assertOk();
        [$first, $second] = $order->payments()->orderBy('id')->pluck('id')->all();

        // The latest payment by default: both payments listed, 120 still due
        $this->as($this->cashier)->get("/orders/{$order->id}/print/receipt")
            ->assertSee('R-' . str_pad((string) $second, 6, '0', STR_PAD_LEFT))
            ->assertSee('Paid · Card')
            ->assertSee(money(120));

        // Reprinting the first one: as it was then, 150 due
        $this->as($this->cashier)->get("/orders/{$order->id}/print/receipt?payment={$first}")
            ->assertSee('R-' . str_pad((string) $first, 6, '0', STR_PAD_LEFT))
            ->assertDontSee('Paid · Card')
            ->assertSee(money(150));

        // Nothing paid yet: an invoice
        $unpaid = $this->createOrder(['payment_amount' => 0]);
        $this->as($this->cashier)->get("/orders/{$unpaid->id}/print/receipt")->assertSee('Invoice')->assertDontSee('Receipt no.');
    }

    public function test_payments_remember_the_cashier(): void
    {
        $order = $this->createOrder();
        $this->as($this->cashier)->postJson("/orders/{$order->id}/add-payment", ['amount' => 10])->assertOk();

        $this->assertSame([$this->admin->id, $this->cashier->id], $order->payments()->orderBy('id')->pluck('received_by')->all());
        $this->as($this->admin)->get("/orders/{$order->id}/details")->assertSee('· Cashier');
    }

    public function test_pos_saves_ready_by_and_weight(): void
    {
        $order = $this->createOrder();
        $this->assertSame('2026-11-05', $order->ready_by->toDateString());
        $this->assertEquals(6.5, $order->weight);

        $this->as($this->admin)->get("/orders/{$order->id}/details")->assertSee('Nov 5, 2026')->assertSee('6.5 kg');

        $this->as($this->admin)->postJson('/pos/orders', [
            'customer_id' => $this->customer->id,
            'order_date' => '2026-11-03',
            'ready_by' => '2026-11-02',
            'items' => [['service_id' => $this->wash->id, 'qty' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors('ready_by');
    }

    public function test_barcode_is_code_128_and_skipped_on_58mm_rolls(): void
    {
        // Start B, O R D - 1, checksum (104 + 47 + 2*50 + 3*36 + 4*13 + 5*17) % 103 = 84, stop
        $this->assertSame([104, 47, 50, 36, 13, 17, 84, 106], Barcode::code128Values('ORD-1'));

        $order = $this->createOrder();
        $svg = base64_decode(substr(Barcode::code128($order->order_number), strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('width="60.5mm"', $svg);   // 17 characters at 0.25 mm a module

        $this->as($this->cashier)->get("/orders/{$order->id}/print/ticket?paper=80mm")->assertSee(Barcode::code128($order->order_number), false);
        $this->as($this->cashier)->get("/orders/{$order->id}/print/ticket?paper=58mm")->assertDontSee(Barcode::code128($order->order_number), false);
    }

    public function test_scanning_a_ticket_qr_code_opens_the_order(): void
    {
        $order = $this->createOrder();

        $this->as($this->cashier)->getJson('/orders/lookup?q=' . urlencode("http://192.168.1.10/orders/{$order->id}/details"))
            ->assertOk()
            ->assertJson(['exact' => route('orders.details', $order)]);
        $this->as($this->cashier)->getJson('/orders/lookup?q=' . urlencode($order->order_number))
            ->assertJson(['exact' => route('orders.details', $order)]);
    }

    public function test_receipt_settings_save_with_a_logo(): void
    {
        $this->as($this->admin)->post('/settings/receipts', [
            'receipt_paper' => '58mm',
            'turnaround_days' => 3,
            'receipt_header' => 'TIN 123-456-789',
            'receipt_terms' => 'No claims after 30 days.',
            'receipt_show_qr' => '1',
            'receipt_logo' => UploadedFile::fake()->image('logo.png', 300, 100),
        ])->assertSessionHas('success');

        $settings = SystemSetting::first();
        $this->assertSame('58mm', $settings->receipt_paper);
        $this->assertSame(3, $settings->turnaround_days);
        $this->assertTrue($settings->receipt_show_qr);
        $this->assertFalse($settings->receipt_show_barcode);
        $this->assertStringStartsWith('data:image/png;base64,', $settings->receiptLogoUri());

        // Documents use the defaults: 58 mm paper, the logo, the header
        $order = $this->createOrder();
        $this->as($this->cashier)->get("/orders/{$order->id}/print/receipt")
            ->assertSee('paper-58mm', false)->assertSee('TIN 123-456-789')->assertSee('data:image/png;base64,', false);

        $this->as($this->admin)->post('/settings/receipts', ['receipt_paper' => 'a5', 'turnaround_days' => 99])
            ->assertSessionHasErrors(['receipt_paper', 'turnaround_days']);
        $this->as($this->cashier)->post('/settings/receipts', ['receipt_paper' => '80mm', 'turnaround_days' => 1]);
        $this->assertSame('58mm', SystemSetting::first()->receipt_paper);

        // Removing the logo deletes the file
        $path = storage_path('app/' . $settings->receipt_logo);
        $this->as($this->admin)->post('/settings/receipts', ['receipt_paper' => '80mm', 'turnaround_days' => 2, 'remove_logo' => '1']);
        $this->assertNull(SystemSetting::first()->receipt_logo);
        $this->assertFileDoesNotExist($path);
    }
}
