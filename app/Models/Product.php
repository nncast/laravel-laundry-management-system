<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'unit_id',
        'purchase_price',
        'available_stock',
        'minimum_stock_level',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'available_stock' => 'integer',
        'minimum_stock_level' => 'integer',
    ];

    // Relationships
    public function category() {
    return $this->belongsTo(Category::class);
}

public function unit() {
    return $this->belongsTo(Unit::class);
}

}

