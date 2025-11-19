<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';

    protected $fillable = [
        'uuid',
        'name',
        'icon',
        'service_type_id',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
        public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

}
