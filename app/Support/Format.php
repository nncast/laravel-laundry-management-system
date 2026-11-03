<?php

namespace App\Support;

use App\Models\SystemSetting;
use Carbon\Carbon;

/**
 * How money, numbers, dates and times are written, from Settings → Region.
 * One instance per request (app(Format::class)); the helpers in app/helpers.php
 * and the browser's window.FMT use the same rules, so every screen and printout agrees.
 */
class Format
{
    /** id => [thousands separator, decimal separator] */
    public const NUMBER_FORMATS = [
        'comma_dot' => [',', '.'],          // 1,234.56
        'dot_comma' => ['.', ','],          // 1.234,56
        'space_comma' => ["\u{202F}", ','], // 1 234,56 (a narrow space that never breaks the line)
    ];

    /** PHP date format => how it reads; month names follow the language */
    public const DATE_FORMATS = [
        'M j, Y' => 'Nov 3, 2026',
        'm/d/Y' => 'MM/DD/YYYY',
        'd/m/Y' => 'DD/MM/YYYY',
        'Y-m-d' => 'YYYY-MM-DD',
    ];

    // Keys aren't plain numbers: PHP would turn '12' into the integer 12
    public const TIME_FORMATS = ['12h' => 'g:i A', '24h' => 'H:i'];

    public function __construct(
        public readonly string $currency = 'PHP',
        public readonly string $numberFormat = 'comma_dot',
        public readonly string $dateFormat = 'M j, Y',
        public readonly string $timeFormat = '12h',
    ) {
    }

    /** "auto" number and date formats: the language's own (config/locales.php). */
    public const AUTO = 'auto';

    public static function fromSettings(SystemSetting $settings): self
    {
        $pick = fn ($value, array $allowed, string $default) => in_array($value, $allowed, true) ? $value : $default;
        $language = config('locales.' . app()->getLocale()) ?? config('locales.en');

        return new self(
            $pick($settings->currency, array_keys(config('currencies')), 'PHP'),
            $settings->number_format === self::AUTO ? $language['number'] : $pick($settings->number_format, array_keys(self::NUMBER_FORMATS), 'comma_dot'),
            $settings->date_format === self::AUTO ? $language['date'] : $pick($settings->date_format, array_keys(self::DATE_FORMATS), 'M j, Y'),
            $pick($settings->time_format, array_keys(self::TIME_FORMATS), '12h'),
        );
    }

    /** ['code', 'name', 'symbol', 'decimals', 'bills'] for a currency (the business one by default). */
    public function currencyInfo(?string $code = null): array
    {
        $code = strtoupper($code ?: $this->currency);

        return ['code' => $code] + (config("currencies.$code") ?? ['name' => $code, 'symbol' => $code . ' ', 'decimals' => 2, 'bills' => []]);
    }

    public function decimals(?string $code = null): int
    {
        return $this->currencyInfo($code)['decimals'];
    }

    public function number(float|int|string|null $value, int $decimals = 0): string
    {
        [$thousands, $decimal] = self::NUMBER_FORMATS[$this->numberFormat];

        return number_format((float) $value, $decimals, $decimal, $thousands);
    }

    /** "₱1,250.00", "¥1,250", "€1.250,00"; $whole drops the decimals (dashboard tiles). */
    public function money(float|int|string|null $amount, ?string $currency = null, bool $whole = false): string
    {
        $info = $this->currencyInfo($currency);
        $value = round((float) $amount, $whole ? 0 : $info['decimals']);
        $text = $info['symbol'] . $this->number(abs($value), $whole ? 0 : $info['decimals']);

        return $value < 0 ? '-' . $text : $text;
    }

    public function date($date): string
    {
        return $date ? Carbon::parse($date)->translatedFormat($this->dateFormat) : '';
    }

    public function time($date): string
    {
        return $date ? Carbon::parse($date)->translatedFormat(self::TIME_FORMATS[$this->timeFormat]) : '';
    }

    public function dateTime($date): string
    {
        return $date ? $this->date($date) . ' ' . $this->time($date) : '';
    }

    /** The same rules for the browser (window.FMT), so live totals match the server's. */
    public function forScript(): array
    {
        $info = $this->currencyInfo();
        [$thousands, $decimal] = self::NUMBER_FORMATS[$this->numberFormat];

        return [
            'currency' => $info['code'],
            'symbol' => $info['symbol'],
            'decimals' => $info['decimals'],
            'thousands' => $thousands,
            'decimal' => $decimal,
        ];
    }
}
