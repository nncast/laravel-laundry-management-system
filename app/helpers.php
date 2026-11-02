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
