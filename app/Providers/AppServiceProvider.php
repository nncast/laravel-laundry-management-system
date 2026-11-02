<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Support\Format;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The system settings: cached between requests (cleared when they are saved)
        // and loaded at most once per request, however many views use them.
        $this->app->singleton('system.settings', function () {
            try {
                return Cache::rememberForever('system.settings', fn () => SystemSetting::first() ?? new SystemSetting());
            } catch (\Throwable $e) {
                // Table not migrated yet (fresh install) – fall back to defaults
                return new SystemSetting();
            }
        });

        // Money, number and date formats from Settings → Region (money(), fmt_date()… in app/helpers.php)
        $this->app->singleton(Format::class, fn ($app) => Format::fromSettings($app->make('system.settings')));
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        // The app does not load Tailwind/Bootstrap, so use our own lightweight pagination markup
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.default');

        // Only the views that actually use $system need it
        View::composer(['layouts.app', 'login', 'pos'], function ($view) {
            $view->with('system', $this->app->make('system.settings'));
        });
    }
}
