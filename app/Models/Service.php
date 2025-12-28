<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'service_type_id',
        'is_active',
    ];

    // Automatically append the icon URL
    protected $appends = ['icon_url'];

    // Relationship: a service belongs to a service type
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    // Accessor: get the full URL for the icon or fallback placeholder
    public function getIconUrlAttribute()
    {
        // If icon is set in DB, use it; otherwise fallback to placeholder
        return asset($this->icon ?? 'images/services/placeholder.jpg');
    }
}
