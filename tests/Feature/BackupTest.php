<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Restore drops and recreates tables, which must run outside a wrapping test
 * transaction (as it does in production), so this class migrates a fresh
 * database per test instead of using RefreshDatabase.
 */
class BackupTest extends TestCase
{
    use DatabaseMigrations;

    private Staff $admin;

    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep test backups out of the real storage/app/backups folder
        $this->storage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laundry-backup-test-' . uniqid();
        $this->app->useStoragePath($this->storage);

        SystemSetting::create(['business_name' => 'Test Laundry']);
        $this->admin = Staff::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\File::deleteDirectory($this->storage);

        parent::tearDown();
    }

    private function asAdmin(): static
    {
        return $this->withSession(['staff' => ['id' => $this->admin->id, 'name' => 'Admin', 'role' => 'admin']]);
    }

    public function test_backup_contains_real_data_and_restore_round_trips(): void
    {
        $type = ServiceType::create(['name' => 'Laundry', 'is_active' => true]);
        $wash = Service::create(['name' => 'Wash', 'service_type_id' => $type->id, 'price' => 100, 'is_active' => true]);
        $addon = Addon::create(['name' => 'Softener', 'price' => 20, 'is_active' => true]);
        $customer = Customer::create(['name' => 'Juan']);
        $note = "It's a \"quoted\"; note\nwith newline -- and dashes";

        $orderId = $this->asAdmin()->postJson('/pos/orders', [
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'notes' => $note,
            'items' => [['service_id' => $wash->id, 'qty' => 2]],
            'addons' => [['addon_id' => $addon->id]],
            'payment_amount' => 50,
        ])->assertOk()->json('order.id');
        $order = Order::findOrFail($orderId);

        $response = $this->asAdmin()->get('/backup/download');
        $response->assertOk();
        $sql = file_get_contents($response->baseResponse->getFile()->getPathname());

        $this->assertStringStartsWith('-- Laundry Management System Database Backup', $sql);
        $this->assertStringContainsString('CREATE TABLE "orders"', $sql);
        $this->assertStringContainsString('INSERT INTO "orders"', $sql);
        $this->assertStringContainsString($order->order_number, $sql);
        $this->assertStringNotContainsString('INSERT INTO "sessions"', $sql);

        // Change data after the backup
        Order::query()->delete();
        Customer::create(['name' => 'Created after backup']);
        $this->assertSame(0, Order::count());

        $file = UploadedFile::fake()->createWithContent('backup.sql', $sql);
        $this->asAdmin()
            ->post('/backup/restore', ['backup_file' => $file, 'confirm' => '1'])
            ->assertRedirect(route('login'));

        $restored = Order::first();
        $this->assertNotNull($restored);
        $this->assertSame($order->order_number, $restored->order_number);
        $this->assertSame($note, $restored->notes);
        $this->assertEquals(50, $restored->paid_amount);
        $this->assertEquals(220, $restored->total);
        $this->assertSame(1, $restored->items()->count());
        $this->assertSame(1, $restored->addons()->count());
        $this->assertSame(1, $restored->payments()->count());
        $this->assertFalse(Customer::where('name', 'Created after backup')->exists());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
    }

    public function test_restore_requires_confirmation_and_rejects_foreign_files(): void
    {
        $file = UploadedFile::fake()->createWithContent('evil.sql', 'DROP TABLE orders;');

        $this->asAdmin()->post('/backup/restore', ['backup_file' => $file])
            ->assertSessionHasErrors('confirm');

        $this->asAdmin()->post('/backup/restore', ['backup_file' => $file, 'confirm' => '1'])
            ->assertSessionHas('error');

        $this->assertTrue(DB::getSchemaBuilder()->hasTable('orders'));
    }

    public function test_settings_page_shows_last_backup_time(): void
    {
        $this->asAdmin()->get('/settings/mastersettings')->assertOk();
        $this->asAdmin()->get('/backup/download')->assertOk();
        $this->asAdmin()->get('/settings/mastersettings')->assertOk()->assertSee('Last backup:');
    }
}
