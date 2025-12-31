<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

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
        'total' => 'decimal:2',
        'qty' => 'integer',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service for the item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Update the total when attributes change.
     */
    protected static function booted(): void
    {
        static::saving(function (OrderItem $item) {
            $item->total = $item->price * $item->rate * $item->qty;
        });
    }

    /**
     * Increase quantity.
     */
    public function increaseQty(int $amount = 1): self
    {
        $this->qty += $amount;
        $this->save();
        return $this;
    }

    /**
     * Decrease quantity.
     */
    public function decreaseQty(int $amount = 1): self
    {
        $this->qty = max(1, $this->qty - $amount);
        $this->save();
        return $this;
    }

    /**
     * Update quantity.
     */
    public function updateQty(int $quantity): self
    {
        $this->qty = max(1, $quantity);
        $this->save();
        return $this;
    }

    /**
     * Get the item name from service.
     */
    public function getNameAttribute(): string
    {
        return $this->service->name ?? 'Unknown Service';
    }
}