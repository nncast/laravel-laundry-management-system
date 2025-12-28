<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'service_id',
        'price',
        'rate',
        'qty',
        'total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'rate' => 'decimal:2',
        'qty' => 'integer',
        'total' => 'decimal:2',
    ];

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship with OrderAddons (if you want to get addons for this specific item)
     */
    public function addons()
    {
        return $this->hasMany(OrderAddon::class);
    }

    /**
     * Get formatted price attribute
     */
    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get formatted total attribute
     */
    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total, 2);
    }

    /**
     * Get formatted rate attribute (if rate is percentage or multiplier)
     */
    public function getFormattedRateAttribute()
    {
        if ($this->rate == 1) {
            return '100%';
        }
        return ($this->rate * 100) . '%';
    }

    /**
     * Get service name through relationship
     */
    public function getServiceNameAttribute()
    {
        return $this->service ? $this->service->name : 'N/A';
    }

    /**
     * Calculate total automatically before saving
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->calculateTotal();
        });
    }

    /**
     * Calculate the total based on price, rate, and quantity
     */
    public function calculateTotal()
    {
        $this->total = ($this->price * $this->rate) * $this->qty;
    }

    /**
     * Scope to get items with their service
     */
    public function scopeWithService($query)
    {
        return $query->with('service');
    }

    /**
     * Scope to get items for a specific order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }
}