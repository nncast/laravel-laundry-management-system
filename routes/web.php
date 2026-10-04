<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    StaffController,
    CustomerController,
    CategoryController,
    UnitController,
    ProductController,
    ServiceController,
    ServiceTypeController,
    PosController,
    SystemSettingController,
    AddonController,
    OrderController,
    DashboardController,
    BackupController,
    DailyReportController,
    OrderReportController,
    SalesReportController
};

// ---------- PUBLIC ROUTES ----------
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('login.post');

// ---------- AUTHENTICATED ROUTES (all roles) ----------
Route::middleware('auth.staff')->group(function () {

    // Dashboard + Logout
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart-data/{period}', [DashboardController::class, 'getChartData'])
        ->whereIn('period', ['week', 'month', 'year'])
        ->name('dashboard.chart');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Home route
    Route::get('/home', fn () => redirect()->route('dashboard'))->name('home');

    // ---------- ORDERS MANAGEMENT ----------
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('orders.index');
        Route::get('/orders/{order}', 'show')->name('orders.show');
        Route::get('/orders/{order}/details', 'details')->name('orders.details');
        Route::get('/orders/{order}/print', 'print')->name('orders.print');
        Route::post('/orders/{order}/update-status', 'updateStatus')->name('orders.update.status');
        Route::post('/orders/{order}/add-payment', 'addPayment')->name('orders.add.payment');
        Route::post('/orders/{order}/add-notes', 'addNotes')->name('orders.add.notes');
        Route::delete('/orders/{order}', 'destroy')
            ->middleware('role:manager,admin')
            ->name('orders.destroy');
    });

    // ---------- POS ----------
    Route::controller(PosController::class)->group(function () {
        Route::get('/pos', 'index')->name('pos.index');
        Route::get('/pos/addons/active', 'getActiveAddons')->name('pos.addons.active');
        Route::get('/pos/check-auth', 'checkAuth')->name('pos.check.auth');
        Route::get('/pos/{order}/edit', 'edit')->name('pos.edit');
        Route::put('/pos/{order}', 'update')->name('pos.update');
        Route::post('/pos/orders', 'createOrder')->name('pos.orders.create');
        Route::post('/pos', 'createOrder')->name('pos.store');
    });

    // ---------- CUSTOMERS ----------
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/customers', 'index')->name('customers.index');
        Route::get('/customers/search', 'search')->name('customers.search');
        Route::post('/customers', 'store')->name('customers.store');
        Route::put('/customers/{customer}', 'update')->name('customers.update');
        Route::delete('/customers/{customer}', 'destroy')
            ->middleware('role:manager,admin')
            ->name('customers.destroy');
    });

    // ---------- MANAGER + ADMIN ----------
    Route::middleware('role:manager,admin')->group(function () {

        // ---------- INVENTORY ----------
        Route::prefix('inventory')->group(function () {

            Route::controller(ProductController::class)->group(function () {
                Route::get('/products', 'index')->name('products.index');
                Route::post('/products', 'store')->name('products.store');
                Route::put('/products', 'update')->name('products.update');
                Route::delete('/products', 'destroy')->name('products.destroy');
            });

            Route::controller(CategoryController::class)->group(function () {
                Route::get('/categories', 'index')->name('categories.index');
                Route::post('/categories', 'store')->name('categories.store');
                Route::put('/categories', 'update')->name('categories.update');
                Route::delete('/categories', 'destroy')->name('categories.destroy');
            });

            Route::controller(UnitController::class)->group(function () {
                Route::get('/units', 'index')->name('units.index');
                Route::post('/units', 'store')->name('units.store');
                Route::put('/units', 'update')->name('units.update');
                Route::delete('/units', 'destroy')->name('units.destroy');
            });
        });

        // ---------- SERVICES ----------
        Route::prefix('services')->group(function () {

            Route::controller(ServiceTypeController::class)->group(function () {
                Route::get('/type', 'index')->name('services.type');
                Route::post('/type', 'store')->name('services.type.store');
                Route::put('/type', 'update')->name('services.type.update');
                Route::delete('/type', 'destroy')->name('services.type.destroy');
            });

            Route::controller(ServiceController::class)->group(function () {
                Route::get('/list', 'index')->name('services.list');
                Route::post('/list', 'store')->name('services.store');
                Route::put('/list/{id}', 'update')->name('services.update');
                Route::delete('/list/{id}', 'destroy')->name('services.destroy');
            });

            Route::controller(AddonController::class)->group(function () {
                Route::get('/addons', 'index')->name('services.addons');
                Route::post('/addons', 'store')->name('services.addons.store');
                Route::put('/addons/{id}', 'update')->name('services.addons.update');
                Route::delete('/addons/{id}', 'destroy')->name('services.addons.destroy');
            });
        });
    });

    // ---------- ADMIN ONLY ----------
    Route::middleware('role:admin')->group(function () {

        // ---------- STAFF ----------
        Route::controller(StaffController::class)->prefix('staff')->group(function () {
            Route::get('/admin', 'index')->name('staff.index');
            Route::post('/store', 'store')->name('staff.store');
            Route::put('/{staff}', 'update')->name('staff.update');
            Route::delete('/{staff}', 'destroy')->name('staff.destroy');
        });

        // ---------- BACKUP ----------
        Route::controller(BackupController::class)->prefix('backup')->group(function () {
            Route::get('/download', 'download')->name('backup.download');
            Route::post('/restore', 'restore')->name('backup.restore');
        });

        // ---------- REPORTS ----------
        Route::prefix('reports')->group(function () {
            Route::get('/daily', [DailyReportController::class, 'index'])->name('reports.daily');
            Route::get('/daily/download', [DailyReportController::class, 'download'])->name('reports.daily.download');

            Route::get('/order', [OrderReportController::class, 'index'])->name('reports.orders');
            Route::get('/order/download', [OrderReportController::class, 'download'])->name('reports.orders.download');

            Route::get('/sales', [SalesReportController::class, 'index'])->name('reports.sales');
            Route::get('/sales/download', [SalesReportController::class, 'download'])->name('reports.sales.download');
            Route::get('/sales/api', [SalesReportController::class, 'apiData'])->name('reports.sales.api');
        });

        // ---------- SETTINGS ----------
        Route::prefix('settings')->group(function () {
            Route::get('/system', [SystemSettingController::class, 'edit'])->name('settings.system.edit');
            Route::post('/system', [SystemSettingController::class, 'update'])->name('settings.update');
            Route::get('/mastersettings', [SystemSettingController::class, 'edit'])->name('settings.mastersettings');
        });
    });
});

// Catch-all route for undefined routes
Route::fallback(function () {
    return redirect()->route('login');
});
