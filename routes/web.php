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
    OrderController
};

use App\Http\Middleware\AuthStaff;

// ---------- PUBLIC ROUTES ----------
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ---------- AUTHENTICATED ROUTES ----------
Route::middleware(AuthStaff::class)->group(function () {

    // Dashboard + Logout
    Route::view('/home', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---------- SALES ----------
    Route::view('/orders', 'orders')->name('sales.orders');
    
    // Order Management Routes
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/list', 'index')->name('orders.index');
        Route::get('/orders/{order}', 'show')->name('orders.show');
        Route::post('/orders/{order}/update-status', 'updateStatus')->name('orders.update.status');
        Route::post('/orders/{order}/add-payment', 'addPayment')->name('orders.add.payment');
    });

    // ---------- POS ----------
    Route::controller(PosController::class)->group(function () {
        Route::get('/pos', 'index')->name('pos');
        Route::post('/pos/orders', 'createOrder')->name('pos.orders.store');
        Route::get('/pos/addons/active', 'getActiveAddons')->name('pos.addons.active');
        Route::get('/pos/check-auth', 'checkAuth')->name('pos.check.auth');
    });

    // ---------- CUSTOMERS ----------
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/customers', 'index')->name('customers.index');
        Route::post('/customers', 'store')->name('customers.store');
        Route::put('/customers/{customer}', 'update')->name('customers.update');
        Route::delete('/customers/{customer}', 'destroy')->name('customers.destroy');
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
    Route::prefix('staff')->controller(StaffController::class)->group(function () {
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
    });

    // ---------- SETTINGS ----------
    Route::prefix('settings')->group(function () {
        Route::get('/system', [SystemSettingController::class, 'edit'])
            ->name('settings.system.edit');

        Route::post('/system', [SystemSettingController::class, 'update'])
            ->name('settings.update');

        Route::view('/filetools', 'settings-filetools')->name('settings.filetools');
        Route::get('/mastersettings', [SystemSettingController::class, 'edit'])->name('settings.mastersettings');
    });

    Route::post('/settings/filetools/upload', [App\Http\Controllers\FileToolsController::class, 'upload'])
        ->name('filetools.upload');
});