<?php

namespace App\Http\Middleware;

use App\Support\Format;
use Carbon\Carbon;
use Closure;

/**
 * The language of this request: the staff member's own choice, else the one
 * picked on the sign-in page, else the shop's default (Settings → Language).
 */
class SetLocale
{
    public function handle($request, Closure $next)
    {
        $settings = app('system.settings');
        $enabled = $settings->enabledLocales();
        $locale = collect([session('staff.locale'), session('locale'), $settings->default_locale])
            ->first(fn ($code) => in_array($code, $enabled, true)) ?? $enabled[0];
        $fallback = array_key_exists((string) $settings->fallback_locale, config('locales')) ? $settings->fallback_locale : 'en';

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        app()->forgetInstance(Format::class);   // "Automatic" number and date formats follow the language

        // Strings are keyed by their English text, so a missing translation shows English;
        // the shop's fallback language, when it isn't English, is tried first
        app('translator')->handleMissingKeysUsing(fn ($key, $replace, $missingIn) => $fallback !== 'en' && !in_array($missingIn, ['en', $fallback], true)
            ? app('translator')->get($key, [], $fallback)
            : null);

        return $next($request);
    }
}
