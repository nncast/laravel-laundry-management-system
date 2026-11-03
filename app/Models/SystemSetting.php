<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'business_name',
        'address',
        'contact',
        'favicon',
        'currency',
        'number_format',
        'date_format',
        'time_format',
        'receipt_logo',
        'receipt_header',
        'receipt_thank_you',
        'receipt_footer',
        'receipt_pickup_instructions',
        'receipt_terms',
        'receipt_paper',
        'receipt_show_qr',
        'receipt_show_barcode',
        'turnaround_days',
        'default_locale',
        'fallback_locale',
        'enabled_locales',
    ];

    /** The same defaults as the database, for a shop that has not saved settings yet. */
    protected $attributes = [
        'receipt_paper' => '80mm',
        'receipt_show_qr' => true,
        'receipt_show_barcode' => true,
        'turnaround_days' => 2,
        'default_locale' => 'en',
        'fallback_locale' => 'en',
    ];

    protected $casts = [
        'receipt_show_qr' => 'boolean',
        'receipt_show_barcode' => 'boolean',
        'turnaround_days' => 'integer',
        'enabled_locales' => 'array',
    ];

    /** Settings are cached (AppServiceProvider); saving them clears the cached copy and this request's. */
    protected static function booted(): void
    {
        $forget = function () {
            Cache::forget('system.settings');
            app()->forgetInstance('system.settings');
        };
        static::saved($forget);
        static::deleted($forget);
    }

    /** The languages staff can pick (Settings → Language): codes from config/locales.php, all of them by default. */
    public function enabledLocales(): array
    {
        $available = array_keys(config('locales'));

        return array_values(array_intersect($available, $this->enabled_locales ?: $available)) ?: ['en'];
    }

    /** The receipt logo as a data: URI (the file is kept in storage/app, not public/), or null. */
    public function receiptLogoUri(): ?string
    {
        $path = $this->receipt_logo ? storage_path('app/' . $this->receipt_logo) : null;

        return $path && is_file($path)
            ? 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path))
            : null;
    }
}

