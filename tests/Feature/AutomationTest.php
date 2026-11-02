<?php

namespace Tests\Feature;

use App\Http\Controllers\BackupController;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Staff;
use App\Models\SystemSetting;
use App\Models\Unit;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The automatic parts of the app: the dashboard's "Needs attention" checks and
 * the daily backup that runs without a cron job. Like BackupTest, it migrates a
 * fresh database per test: mixing RefreshDatabase in front of BackupTest's
 * DatabaseMigrations leaves later RefreshDatabase tests without tables.
 */
class AutomationTest extends TestCase
{
    use DatabaseMigrations;

    private Staff $admin;

    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep test backups out of the real storage/app/backups folder
        $this->storage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laundry-automation-test-' . uniqid();
        $this->app->useStoragePath($this->storage);

        SystemSetting::create(['business_name' => 'Test Laundry']);
        $this->admin = Staff::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);

        parent::tearDown();
    }

    private function order(string $status, int $daysAgo, float $total = 100, float $paid = 0): Order
    {
        $customer = Customer::firstOrCreate(['name' => 'Juan'], ['contact' => '09170000000']);

        return Order::create([
            'customer_id' => $customer->id, 'staff_id' => $this->admin->id, 'status' => $status,
            'order_date' => now()->subDays($daysAgo)->toDateString(), 'total' => $total, 'paid_amount' => $paid,
        ]);
    }

    public function test_dashboard_finds_what_needs_attention_and_backs_up(): void
    {
        $stuckPending = $this->order('pending', 1);
        $stuckProcessing = $this->order('processing', 3);
        $this->order('pending', 0);         // today: not stuck yet
        $this->order('processing', 2);      // under 3 days: not stuck yet
        $unpaid = $this->order('completed', 4, 200, 50);
        $this->order('completed', 4, 200, 200);

        $category = Category::create(['name' => 'Soap']);
        $unit = Unit::create(['name' => 'Pack', 'short_form' => 'pk']);
        $product = fn (string $name, int $stock, int $min, string $status = 'active') => Product::create([
            'name' => $name, 'category_id' => $category->id, 'unit_id' => $unit->id, 'purchase_price' => 10,
            'available_stock' => $stock, 'minimum_stock_level' => $min, 'status' => $status,
        ]);
        $bleach = $product('Bleach', 0, 4);
        $product('Softener', 9, 5);
        $product('Old soap', 0, 4, 'inactive');

        $this->withSession(['staff' => ['id' => $this->admin->id, 'name' => 'Admin', 'role' => 'admin']])
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertViewHas('stuckOrders', fn ($orders) => $orders->pluck('id')->sort()->values()->all() === [$stuckPending->id, $stuckProcessing->id])
            ->assertViewHas('unpaidOrders', fn ($orders) => $orders->pluck('id')->all() === [$unpaid->id])
            ->assertViewHas('lowStock', fn ($products) => $products->pluck('id')->all() === [$bleach->id]);

        // The dashboard request also made the first automatic backup
        $this->assertCount(1, glob(storage_path('app/backups') . DIRECTORY_SEPARATOR . 'backup_*.sql'));
    }

    public function test_dashboard_says_all_caught_up_when_nothing_is_wrong(): void
    {
        $this->order('completed', 2, 100, 100);

        $this->withSession(['staff' => ['id' => $this->admin->id, 'name' => 'Admin', 'role' => 'admin']])
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('All caught up')
            ->assertViewHas('stuckOrders', fn ($orders) => $orders->isEmpty())
            ->assertViewHas('unpaidOrders', fn ($orders) => $orders->isEmpty())
            ->assertViewHas('lowStock', fn ($products) => $products->isEmpty());
    }

    public function test_automatic_backup_runs_only_when_the_last_one_is_a_day_old(): void
    {
        $dir = storage_path('app/backups');

        $this->assertNotNull(BackupController::backupIfDue(), 'no backup yet, so one is made');
        $this->assertNull(BackupController::backupIfDue(), 'a fresh backup exists, so nothing happens');

        foreach (glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql') as $file) {
            touch($file, time() - 25 * 3600);
        }
        $this->travel(1)->hours();   // new file name

        $this->assertNotNull(BackupController::backupIfDue(), 'the newest backup is a day old, so a new one is made');
        $this->assertCount(2, glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql'));
    }
}
