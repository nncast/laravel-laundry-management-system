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
    ];

    /** Settings are cached (AppServiceProvider); saving them clears the cached copy. */
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('system.settings'));
        static::deleted(fn () => Cache::forget('system.settings'));
    }
}

