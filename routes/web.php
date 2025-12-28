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
    SystemSettingController


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
    Route::view('/pos', 'pos')->name('sales.pos');
    
Route::get('/pos', [PosController::class, 'index'])->name('pos');

    // ---------- CUSTOMERS ----------
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/customers', 'index')->name('customers.index');
        Route::post('/customers', 'store')->name('customers.store');
        Route::put('/customers/{customer}', 'update')->name('customers.update');
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
    Route::put('/list/{id}', 'update')->name('services.update');        // add {id}
    Route::delete('/list/{id}', 'destroy')->name('services.destroy');    // add {id}
});


        Route::view('/addons', 'services-addons')->name('services.addons');
    });

    // ---------- STAFF ----------
    Route::prefix('staff')->controller(StaffController::class)->group(function () {
        Route::get('/admin', 'index')->name('staff.index');
        Route::post('/store', 'store')->name('staff.store');
        Route::put('/{staff}', 'update')->name('staff.update');
        Route::delete('/{staff}', 'destroy')->name('staff.destroy');
    });

    Route::view('/staff/cashier', 'users-cashier')->name('staff.cashier');
    Route::view('/staff/manager', 'users-manager')->name('staff.manager');

    // ---------- REPORTS ----------
    Route::prefix('reports')->group(function () {
        Route::view('/daily', 'report-daily')->name('reports.daily');
        Route::view('/sales', 'report-sales')->name('reports.sales');
        Route::view('/order', 'report-orders')->name('reports.orders');
    });

    // ---------- SETTINGS ----------
Route::prefix('settings')->group(function () {
    
    Route::get('/system', [\App\Http\Controllers\SystemSettingController::class, 'edit'])
        ->name('settings.system.edit');

    Route::post('/system', [\App\Http\Controllers\SystemSettingController::class, 'update'])
        ->name('settings.update');

    Route::view('/filetools', 'settings-filetools')->name('settings.filetools');
    Route::get('/mastersettings', [SystemSettingController::class, 'edit'])->name('settings.mastersettings');
});

Route::post('/settings/filetools/upload', [App\Http\Controllers\FileToolsController::class, 'upload'])
    ->name('filetools.upload');

});
