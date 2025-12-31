<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'order_type',
        'order_date',
        'subtotal',
        'discount',
        'total',
        'paid_amount',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function ($order) {
            // Generate order number if not set
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
            
            // Set order date to today if not set
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
        });

        static::saving(function ($order) {
            // Calculate totals before saving
            $order->calculateTotals();
        });
    }

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user (staff) who created the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the addons for the order.
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'order_addons')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    /**
     * Calculate all totals for the order.
     */
    public function calculateTotals(): void
    {
        // Calculate items subtotal
        $itemsTotal = $this->items->sum('total');
        
        // Calculate addons total
        $addonsTotal = $this->addons->sum(function ($addon) {
            return $addon->pivot->price;
        });
        
        $this->subtotal = $itemsTotal + $addonsTotal;
        $this->total = $this->subtotal - $this->discount;
        
        // Ensure total is not negative
        if ($this->total < 0) {
            $this->total = 0;
        }
    }

    /**
     * Add a service to the order.
     */
    public function addService(Service $service, int $quantity = 1): OrderItem
    {
        // Check if service already exists in order
        $existingItem = $this->items()
            ->where('service_id', $service->id)
            ->first();

        if ($existingItem) {
            // Update quantity
            $existingItem->qty += $quantity;
            $existingItem->total = $existingItem->price * $existingItem->qty;
            $existingItem->save();
            return $existingItem;
        }

        // Create new order item
        return $this->items()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'rate' => 1, // Default rate
            'qty' => $quantity,
            'total' => $service->price * $quantity,
        ]);
    }

    /**
     * Add an addon to the order.
     */
    public function addAddon(Addon $addon): void
    {
        $this->addons()->attach($addon->id, [
            'price' => $addon->price
        ]);
    }

    /**
     * Apply discount to the order.
     */
    public function applyDiscount(float $discount): void
    {
        $this->discount = $discount;
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Add a payment to the order.
     */
    public function addPayment(float $amount): Payment
    {
        $payment = $this->payments()->create([
            'amount' => $amount
        ]);
        
        // Update paid amount
        $this->paid_amount = $this->payments()->sum('amount');
        $this->save();
        
        return $payment;
    }

    /**
     * Calculate the balance (remaining amount to pay).
     */
    public function getBalanceAttribute(): float
    {
        return (float) $this->total - (float) $this->paid_amount;
    }

    /**
     * Check if order is fully paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    /**
     * Get the order status as a human-readable label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
            default => ucfirst($this->status)
        };
    }

    /**
     * Check if order can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}