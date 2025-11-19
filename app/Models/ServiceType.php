<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    // Table associated with the model (optional if table name is plural of model)
    protected $table = 'service_types';

    // Columns that can be mass-assigned
    protected $fillable = [
        'name',
        'is_active',
    ];

    // Cast attributes to specific data types
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Optional: scope for active service types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship with Services
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}