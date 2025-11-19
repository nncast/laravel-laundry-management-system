<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthStaff;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTypeController;

// ---------- PUBLIC ROUTES ----------
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// ---------- AUTHENTICATED ROUTES ----------
Route::middleware([AuthStaff::class])->group(function () {

    // DASHBOARD
    Route::view('/home', 'dashboard')->name('dashboard');

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---------- SALES ----------
        Route::view('/orders', 'orders')->name('sales.orders');
        Route::view('/pos', 'pos')->name('sales.pos');

    // ---------- CUSTOMERS ----------
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // ---------- INVENTORY ----------
    Route::prefix('inventory')->group(function () {

        // PRODUCTS CRUD
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products', [ProductController::class, 'update'])->name('products.update'); // ID in payload
        Route::delete('/products', [ProductController::class, 'destroy'])->name('products.destroy');

        // CATEGORIES CRUD
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // UNITS CRUD
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::put('/units', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // ---------- SERVICES ----------
    Route::prefix('services')->group(function () {

        // Service Types CRUD (ID in request body, not URL)
        Route::get('/type', [ServiceTypeController::class, 'index'])->name('services.type');
        Route::post('/type', [ServiceTypeController::class, 'store'])->name('services.type.store');
        Route::put('/type', [ServiceTypeController::class, 'update'])->name('services.type.update');  // ID in payload
        Route::delete('/type', [ServiceTypeController::class, 'destroy'])->name('services.type.destroy'); // ID in payload

        Route::get('/list', [ServiceController::class, 'index'])->name('services.list');
        Route::post('/list', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/list', [ServiceController::class, 'update'])->name('services.update'); // ID in payload
        Route::delete('/list', [ServiceController::class, 'destroy'])->name('services.destroy'); // ID in payload

        Route::view('/addons', 'services-addons')->name('services.addons');
    });

    // ---------- USERS / STAFF ----------
    Route::prefix('staff')->group(function () {
        Route::get('/admin', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/store', [StaffController::class, 'store'])->name('staff.store');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

        Route::view('/cashier', 'users-cashier')->name('staff.cashier');
        Route::view('/manager', 'users-manager')->name('staff.manager');
    });

    // ---------- REPORTS ----------
    Route::prefix('reports')->group(function () {
        Route::view('/daily', 'report-daily')->name('reports.daily');
        Route::view('/sales', 'report-sales')->name('reports.sales');
        Route::view('/order', 'report-orders')->name('reports.orders');
    });

    // ---------- SETTINGS ----------
    Route::prefix('settings')->group(function () {
        Route::view('/filetools', 'settings-filetools')->name('settings.filetools');
        Route::view('/mastersettings', 'settings-mastersettings')->name('settings.mastersettings');
    });

});
