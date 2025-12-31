<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddon extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_addons';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'addon_id',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the order addon.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the addon that owns the order addon.
     */
    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    /**
     * Get formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get addon name through relationship.
     */
    public function getAddonNameAttribute(): string
    {
        return $this->addon ? $this->addon->name : 'N/A';
    }

    /**
     * Get addon details including if it's still active.
     */
    public function getAddonDetailsAttribute(): array
    {
        if (!$this->addon) {
            return [
                'name' => 'N/A',
                'is_active' => false,
                'current_price' => 0,
            ];
        }

        return [
            'name' => $this->addon->name,
            'is_active' => $this->addon->is_active,
            'current_price' => $this->addon->price,
            'price_changed' => $this->addon->price != $this->price,
        ];
    }

    /**
     * Scope a query to only include order addons with active addons.
     */
    public function scopeWithActiveAddons($query)
    {
        return $query->whereHas('addon', function ($query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Scope a query to only include order addons from a specific order.
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to only include order addons with a specific addon.
     */
    public function scopeWithAddon($query, $addonId)
    {
        return $query->where('addon_id', $addonId);
    }

    /**
     * Create a new order addon with price from addon if not provided.
     */
    public static function createForOrder($orderId, $addonId, $price = null): self
    {
        $addon = Addon::findOrFail($addonId);
        
        return self::create([
            'order_id' => $orderId,
            'addon_id' => $addonId,
            'price' => $price ?? $addon->price,
        ]);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Update order totals when order addon is created or deleted
        static::created(function (OrderAddon $orderAddon) {
            $orderAddon->order->updateTotals();
        });

        static::deleted(function (OrderAddon $orderAddon) {
            if ($orderAddon->order) {
                $orderAddon->order->updateTotals();
            }
        });
    }
}