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
    ];

    /** The same defaults as the database, for a shop that has not saved settings yet. */
    protected $attributes = [
        'receipt_paper' => '80mm',
        'receipt_show_qr' => true,
        'receipt_show_barcode' => true,
        'turnaround_days' => 2,
    ];

    protected $casts = [
        'receipt_show_qr' => 'boolean',
        'receipt_show_barcode' => 'boolean',
        'turnaround_days' => 'integer',
    ];

    /** Settings are cached (AppServiceProvider); saving them clears the cached copy. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('system.settings'));
        static::deleted(fn () => Cache::forget('system.settings'));
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

