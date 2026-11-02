<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    // Table name (optional if it matches the plural of model)
    protected $table = 'customers';

    // Fields that are mass assignable
    protected $fillable = [
        'name',
        'contact',
        'address'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
