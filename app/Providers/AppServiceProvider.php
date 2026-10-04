<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Load the system settings at most once per request, no matter how
        // many views/partials are rendered.
        $this->app->singleton('system.settings', function () {
            try {
                return SystemSetting::first() ?? new SystemSetting();
            } catch (\Throwable $e) {
                // Table not migrated yet (fresh install) – fall back to defaults
                return new SystemSetting();
            }
        });
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
