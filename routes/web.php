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
    DashboardController,  // Added DashboardController
    FileToolsController    // Added FileToolsController
};

use App\Http\Middleware\AuthStaff;

// ---------- PUBLIC ROUTES ----------
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ---------- AUTHENTICATED ROUTES ----------
Route::middleware(AuthStaff::class)->group(function () {

      // Dashboard + Logout
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // Fixed: Changed from view to controller
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats'); // Added dashboard stats route
    Route::get('/dashboard/chart-data/{period}', [DashboardController::class, 'getChartData'])->name('dashboard.chart'); // Added chart route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---------- ORDERS MANAGEMENT ----------
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('orders.index');
        Route::get('/orders/{order}', 'show')->name('orders.show');
        Route::get('/orders/{order}/details', 'details')->name('orders.details');
        Route::get('/orders/{order}/print', 'print')->name('orders.print');
        Route::post('/orders/{order}/update-status', 'updateStatus')->name('orders.update.status');
        Route::post('/orders/{order}/add-payment', 'addPayment')->name('orders.add.payment');
        Route::post('/orders/{order}/add-notes', 'addNotes')->name('orders.add.notes'); // Added notes route
        Route::delete('/orders/{order}', 'destroy')->name('orders.destroy');
    });

    // ---------- POS ----------
    Route::controller(PosController::class)->group(function () {
        Route::get('/pos', 'index')->name('pos.index');
        Route::get('/pos/{order}/edit', 'edit')->name('pos.edit');
        Route::put('/pos/{order}', 'update')->name('pos.update'); // Fixed: Moved inside group
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
            Route::put('/products/{product}', 'update')->name('products.update'); // Fixed: Added parameter
            Route::delete('/products/{product}', 'destroy')->name('products.destroy'); // Fixed: Added parameter
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'index')->name('categories.index');
            Route::post('/categories', 'store')->name('categories.store');
            Route::put('/categories/{category}', 'update')->name('categories.update'); // Fixed: Added parameter
            Route::delete('/categories/{category}', 'destroy')->name('categories.destroy'); // Fixed: Added parameter
        });

        Route::controller(UnitController::class)->group(function () {
            Route::get('/units', 'index')->name('units.index');
            Route::post('/units', 'store')->name('units.store');
            Route::put('/units/{unit}', 'update')->name('units.update'); // Fixed: Added parameter
            Route::delete('/units/{unit}', 'destroy')->name('units.destroy'); // Fixed: Added parameter
        });
    });

    // ---------- SERVICES ----------
    Route::prefix('services')->group(function () {

        Route::controller(ServiceTypeController::class)->group(function () {
            Route::get('/type', 'index')->name('services.type');
            Route::post('/type', 'store')->name('services.type.store');
            Route::put('/type/{serviceType}', 'update')->name('services.type.update'); // Fixed: Added parameter
            Route::delete('/type/{serviceType}', 'destroy')->name('services.type.destroy'); // Fixed: Added parameter
        });

        Route::controller(ServiceController::class)->group(function () {
            Route::get('/list', 'index')->name('services.list');
            Route::post('/list', 'store')->name('services.store');
            Route::put('/list/{service}', 'update')->name('services.update'); // Fixed: Changed parameter name
            Route::delete('/list/{service}', 'destroy')->name('services.destroy'); // Fixed: Changed parameter name
        });

        // ADDON MANAGEMENT ROUTES (Admin Panel)
        Route::controller(AddonController::class)->group(function () {
            Route::get('/addons', 'index')->name('services.addons');
            Route::post('/addons', 'store')->name('services.addons.store');
            Route::put('/addons/{addon}', 'update')->name('services.addons.update'); // Fixed: Added parameter
            Route::delete('/addons/{addon}', 'destroy')->name('services.addons.destroy'); // Fixed: Added parameter
        });
    });

    // ---------- STAFF ----------
    Route::controller(StaffController::class)->prefix('staff')->group(function () { // Fixed: Combined controller and prefix
        Route::get('/admin', 'index')->name('staff.index');
        Route::post('/store', 'store')->name('staff.store');
        Route::put('/{staff}', 'update')->name('staff.update');
        Route::delete('/{staff}', 'destroy')->name('staff.destroy');
    });

    // ---------- REPORTS ----------
    Route::prefix('reports')->group(function () {
        Route::view('/daily', 'report-daily')->name('reports.daily');
        Route::view('/sales', 'report-sales')->name('reports.sales');
        Route::view('/order', 'report-orders')->name('reports.orders');
        Route::get('/custom', [App\Http\Controllers\ReportController::class, 'custom'])->name('reports.custom'); // Added report controller
    });

    // ---------- SETTINGS ----------
    Route::prefix('settings')->group(function () {
        Route::get('/system', [SystemSettingController::class, 'edit'])
            ->name('settings.system.edit');

        Route::post('/system', [SystemSettingController::class, 'update'])
            ->name('settings.update');

        Route::get('/filetools', [FileToolsController::class, 'index'])->name('settings.filetools'); // Fixed: Changed to controller
        Route::post('/filetools/upload', [FileToolsController::class, 'upload'])->name('filetools.upload'); // Fixed: Moved inside group
        
        Route::get('/mastersettings', [SystemSettingController::class, 'edit'])->name('settings.mastersettings');
    });

    // Home route (redirects to dashboard)
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');
});

// Catch-all route for undefined routes (should be last)
Route::fallback(function () {
    return redirect()->route('login');
});