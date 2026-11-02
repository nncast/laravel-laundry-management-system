<?php

/*
| Formatting helpers for views and controllers (loaded by AppServiceProvider).
| They follow Settings → Region; see App\Support\Format.
*/

use App\Support\Format;

if (!function_exists('money')) {
    /** An amount in the business currency, or in $currency (an order keeps the currency it was taken in). */
    function money($amount, ?string $currency = null, bool $whole = false): string
    {
        return app(Format::class)->money($amount, $currency, $whole);
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(?string $currency = null): string
    {
        return app(Format::class)->currencyInfo($currency)['symbol'];
    }
}

if (!function_exists('money_step')) {
    /** The step for money inputs: "0.01", or "1" for currencies without decimals (yen, rupiah). */
    function money_step(?string $currency = null): string
    {
        $decimals = app(Format::class)->decimals($currency);

        return $decimals > 0 ? '0.' . str_repeat('0', $decimals - 1) . '1' : '1';
    }
}

if (!function_exists('money_input')) {
    /** An amount for an <input type="number"> value: dot decimals, no grouping. */
    function money_input($amount, ?string $currency = null): string
    {
        return number_format((float) $amount, app(Format::class)->decimals($currency), '.', '');
    }
}

if (!function_exists('fmt_number')) {
    function fmt_number($value, int $decimals = 0): string
    {
        return app(Format::class)->number($value, $decimals);
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date($date): string
    {
        return app(Format::class)->date($date);
    }
}

if (!function_exists('fmt_time')) {
    function fmt_time($date): string
    {
        return app(Format::class)->time($date);
    }
}

if (!function_exists('fmt_datetime')) {
    function fmt_datetime($date): string
    {
        return app(Format::class)->dateTime($date);
    }
}

if (!function_exists('asset_v')) {
    /**
     * URL of a file in public/ with its modification time as the version, so browsers
     * can keep it for a year (see public/.htaccess). Serves the minified copy
     * (app.min.css next to app.css, from `npm run minify`) while it was made from the
     * current source: its first line carries the source's SHA-1.
     */
    function asset_v(string $path): string
    {
        $min = preg_replace('/\.(css|js)$/', '.min.$1', $path);
        if ($min !== $path && is_file(public_path($min))) {
            $header = (string) fgets(fopen(public_path($min), 'r'));
            if (str_contains($header, 'sha1:' . sha1(str_replace("\r\n", "\n", file_get_contents(public_path($path)))))) {
                $path = $min;
            }
        }

        return asset($path) . '?v=' . @filemtime(public_path($path));
    }
}
