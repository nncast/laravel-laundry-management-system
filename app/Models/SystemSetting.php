<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

