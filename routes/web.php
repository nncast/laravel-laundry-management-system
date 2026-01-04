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
    FileToolsController,
    BackupController,
    ReportController,
    DailyReportController ,
    OrderReportController,
    SalesReportController
};

use App\Http\Middleware\AuthStaff;

// ---------- PUBLIC ROUTES ----------
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ---------- AUTHENTICATED ROUTES ----------
Route::middleware(AuthStaff::class)->group(function () {

    // Dashboard + Logout
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart-data/{period}', [DashboardController::class, 'getChartData'])->name('dashboard.chart');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---------- ORDERS MANAGEMENT ----------
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('orders.index');
        Route::get('/orders/{order}', 'show')->name('orders.show');
        Route::get('/orders/{order}/details', 'details')->name('orders.details');
        Route::get('/orders/{order}/print', 'print')->name('orders.print');
        Route::post('/orders/{order}/update-status', 'updateStatus')->name('orders.update.status');
        Route::post('/orders/{order}/add-payment', 'addPayment')->name('orders.add.payment');
        Route::post('/orders/{order}/add-notes', 'addNotes')->name('orders.add.notes');
        Route::delete('/orders/{order}', 'destroy')->name('orders.destroy');
    });

    // ---------- POS ----------
    Route::controller(PosController::class)->group(function () {
        Route::get('/pos', 'index')->name('pos.index');
        Route::get('/pos/{order}/edit', 'edit')->name('pos.edit');
        Route::put('/pos/{order}', 'update')->name('pos.update');
        Route::post('/pos/orders', 'createOrder')->name('pos.orders.create');
        Route::post('/pos', 'createOrder')->name('pos.store');
        Route::get('/pos/addons/active', 'getActiveAddons')->name('pos.addons.active');
        Route::get('/pos/check-auth', 'checkAuth')->name('pos.check.auth');
    });

    // ---------- CUSTOMERS ----------
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/customers', 'index')->name('customers.index');
        Route::post('/customers', 'store')->name('customers.store');
        Route::put('/customers/{customer}', 'update')->name('customers.update');
        Route::delete('/customers/{customer}', 'destroy')->name('customers.destroy');
        Route::get('/customers/search', 'search')->name('customers.search'); // Added search route
    });

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

        // ADDON MANAGEMENT ROUTES (Admin Panel)
        Route::controller(AddonController::class)->group(function () {
            Route::get('/addons', 'index')->name('services.addons');
            Route::post('/addons', 'store')->name('services.addons.store');
            Route::put('/addons/{id}', 'update')->name('services.addons.update');
            Route::delete('/addons/{id}', 'destroy')->name('services.addons.destroy');
        });
    });

    // ---------- STAFF ----------
    Route::controller(StaffController::class)->prefix('staff')->group(function () {
        Route::get('/admin', 'index')->name('staff.index');
        Route::post('/store', 'store')->name('staff.store');
        Route::put('/{staff}', 'update')->name('staff.update');
        Route::delete('/{staff}', 'destroy')->name('staff.destroy');
    });

    // ---------- BACKUP ROUTES ----------
    Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');

// ---------- REPORTS ----------
Route::prefix('reports')->group(function () {
    // Daily Report routes
    Route::get('/daily', [DailyReportController::class, 'index'])->name('reports.daily');
    Route::get('/daily/download', [DailyReportController::class, 'download'])->name('reports.daily.download');
    
    // Order Report routes
    Route::get('/order', [OrderReportController::class, 'index'])->name('reports.orders');
    Route::get('/order/download', [OrderReportController::class, 'download'])->name('reports.orders.download');
    
    // Sales Report routes
    Route::get('/sales', [SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('/sales/download', [SalesReportController::class, 'download'])->name('reports.sales.download');
    Route::get('/sales/api', [SalesReportController::class, 'apiData'])->name('reports.sales.api');
    
    // Custom Report routes
    Route::get('/custom', [ReportController::class, 'custom'])->name('reports.custom');
});

    // ---------- SETTINGS ----------
    Route::prefix('settings')->group(function () {
        Route::get('/system', [SystemSettingController::class, 'edit'])
            ->name('settings.system.edit');

        Route::post('/system', [SystemSettingController::class, 'update'])
            ->name('settings.update');

        Route::get('/mastersettings', [SystemSettingController::class, 'edit'])
            ->name('settings.mastersettings');
    });

    // Home route
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');
});

// Catch-all route for undefined routes
Route::fallback(function () {
    return redirect()->route('login');
});