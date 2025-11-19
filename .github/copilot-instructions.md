# Copilot Instructions - Laundry Management System

## Project Overview
Laravel 12 web application for managing laundry operations (Fitters Laundry). Stack: PHP 8.2+, Laravel 12, Vite 7, Tailwind CSS 4, SQLite (default).

## Critical Architecture Notes

### Early-Stage Structure
- **Routes/Web.php Issue**: Currently contains a Blade layout template instead of route definitions. This is non-standard.
- **Incomplete Implementation**: Project has views defined but likely missing corresponding controllers and routes.
- **Action**: Before adding features, establish proper routing structure in `routes/web.php` using Laravel 12 conventions.

### Database & ORM
- **Default**: SQLite with `database/database.sqlite`
- **Alternative**: Configurable via `.env` (DB_CONNECTION=sqlite|mysql|pgsql)
- **Sessions/Cache**: Database-backed (SESSION_DRIVER, CACHE_STORE configs)
- **Migrations**: Located in `database/migrations/` with timestamp naming
- **Models**: PSR-4 autoload from `app/Models/` - create Eloquent models for Orders, Customers, Inventory, Services, etc.

### View Layer - Critical Pattern
- **Blade Templates**: `resources/views/` with shared layout at `resources/views/layouts/app.blade.php`
- **Layout Structure**: 
  - Extends layout with `@extends('layouts.app')`
  - Yields: `@yield('title')`, `@yield('page-title')`, `@yield('active-*')` sections, `@yield('content')`
  - Example: `dashboard.blade.php` uses `@section('active-dashboard', 'active')` for sidebar navigation
- **URL Patterns**: Navigation uses `{{ url('/path') }}` and `request()->is('pattern*')` for active states
- **Sections to Implement**: dashboard, orders, POS, customers, inventory (products/categories/units), services (list/type/addons), users (admin/manager/cashier), reports (daily/sales/orders), settings

### Frontend Stack
- **CSS Framework**: Tailwind CSS 4 via `@tailwindcss/vite` plugin
- **Asset Pipeline**: Vite with `resources/css/app.css` entry point
- **Custom CSS**: `public/css/style.css` (legacy styling for sidebar/layout)
- **JS**: Minimal—`public/js/sidebar.js` handles menu toggles; `resources/js/bootstrap.js` configures Axios
- **Build**: `npm run dev` (watch), `npm run build` (production)

### User/Auth Management
- **Base Model**: `app/Models/User.php` with standard Laravel Authenticatable
- **Fillable**: name, email, password (hashed via bcrypt)
- **Roles Expected**: Admin, Manager, Cashier (views exist but roles not yet implemented—add to User model)
- **Auth Guard**: 'web' session-based (configured in `config/auth.php`)

## Development Workflow

### Setup & Environment
```bash
# Initial setup (uses composer script)
composer run setup

# OR manual steps:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install && npm run build
```

### Local Development
```bash
# Run dev server with file watching, queue listening, and logs
composer run dev
# Runs: php artisan serve + queue:listen + pail + npm run dev (concurrent)

# Single services:
php artisan serve              # Web server (localhost:8000)
npm run dev                    # Vite watch
php artisan queue:listen --tries=1  # Job processing
php artisan pail               # Log streaming
```

### Testing
```bash
composer run test
# Clears config cache then runs phpunit via tests/ directory
# Test structure: tests/Feature/, tests/Unit/
# Config: phpunit.xml sets DB_CONNECTION=sqlite + DB_DATABASE=:memory:
```

### Code Quality
- **Formatting**: Laravel Pint (`vendor/bin/pint`) - not yet configured in scripts
- **Dependencies**: PHPUnit 11.5, Mockery, Collision error handler, Faker for test data

## Key Conventions & Patterns

### Naming & Organization
- **Controllers**: `app/Http/Controllers/` - create domain-specific controllers (OrderController, CustomerController, etc.)
- **Models**: `app/Models/` - one model per entity; use Eloquent relationships
- **Views**: `resources/views/` - match blade filename to route/feature (`orders.blade.php`, `customers.blade.php`)
- **Migrations**: Use timestamp prefix; name descriptively (e.g., `create_orders_table.php`)

### Route Definition Pattern
**Current State**: No routes defined yet. Must add to `routes/web.php`:
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::middleware('auth')->group(function () {
    Route::get('/home', fn() => view('dashboard'))->name('dashboard');
    Route::resource('/orders', OrderController::class);
    // Add other resources
});
```

### Blade Template Pattern
Every view must:
1. Extend layout: `@extends('layouts.app')`
2. Set title: `@section('title', 'Page Name')`
3. Set page-title: `@section('page-title', 'Page Title')`
4. Mark active section: `@section('active-section', 'active')`
5. Add content: `@section('content') ... @endsection`

Example:
```blade
@extends('layouts.app')
@section('title', 'Orders')
@section('page-title', 'Order Management')
@section('active-orders', 'active')

@section('content')
    <div class="table-container">
        {{-- Table markup --}}
    </div>
@endsection
```

### Eloquent Relationships (To Implement)
Design relationships based on domain:
- **Orders**: belongs to Customer, has many OrderItems
- **Customers**: has many Orders
- **Inventory**: Products belong to Categories; has Units
- **Services**: has Addons
- **Users**: has Role (when RBAC added)

## Configuration Reference

### Key Config Files
- `config/app.php` - Timezone (UTC), locale (en), cipher (AES-256-CBC)
- `config/auth.php` - Guards (web), provider (Eloquent users)
- `config/database.php` - Default connection (sqlite), queue connection (database)
- `config/session.php` - Driver (database), lifetime (120 min)
- `config/cache.php` - Store (database)
- `.env` - APP_NAME, APP_DEBUG, APP_URL, DB_* variables

### Important Environment Variables
```
APP_NAME=Fitters Laundry
APP_ENV=local|production
APP_DEBUG=true|false
DB_CONNECTION=sqlite|mysql|pgsql
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

## Common Tasks & File Locations

| Task | File | Notes |
|------|------|-------|
| Add route | `routes/web.php` | Currently misplaced as layout—restructure |
| Create model | `app/Models/YourModel.php` | Run: `php artisan make:model YourModel -m` |
| Create controller | `app/Http/Controllers/YourController.php` | Run: `php artisan make:controller YourController` |
| Add migration | `database/migrations/` | Run: `php artisan make:migration create_table_name` |
| Create view | `resources/views/page.blade.php` | Must extend `layouts.app` |
| Update layout | `resources/views/layouts/app.blade.php` | Affects all child views |
| Add CSS | `resources/css/app.css` | Tailwind configured; runs through Vite |
| Database seed | `database/seeders/DatabaseSeeder.php` | Run: `php artisan db:seed` |

## Debugging Tips

1. **Log Files**: `storage/logs/laravel.log` - check for errors
2. **Query Logging**: Set `DB_DEBUG=true` in `.env`; Tinker: `php artisan tinker` for REPL
3. **Route Debugging**: `php artisan route:list` - view all registered routes
4. **View Errors**: Browser shows detailed stack trace if `APP_DEBUG=true`
5. **Test Isolation**: Tests use in-memory SQLite and cache/session arrays (phpunit.xml)

## Implementation Priority

1. **Fix Routes**: Move layout from `routes/web.php` to `resources/views/layouts/app.blade.php` (if not already done) and define proper route groups
2. **Create Controllers**: OrderController, CustomerController, InventoryController for core features
3. **Build Models**: Order, Customer, Product, Service, ServiceAddon, User
4. **Add Relationships**: Link models via Eloquent
5. **Implement Views**: Populate existing blade files with data from controllers
6. **Add RBAC**: Implement role-based access control for Admin/Manager/Cashier users
7. **Testing**: Write feature tests for each major endpoint

## Notes

- **PSR-4 Autoloading**: App namespace routes to `app/`, Database namespace to `database/`
- **Service Container**: Use dependency injection in controllers via constructor parameters
- **Middleware**: Add auth checks via `middleware('auth')` in route groups
- **CSRF Protection**: All POST/PUT/DELETE forms must include `@csrf` token
- **Validation**: Use Request classes (`php artisan make:request StoreOrderRequest`) for complex validation
