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
        'staff_id',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'balance',
        'is_paid',
        'status_label',
        'can_edit',
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

        static::created(function ($order) {
            // Ensure totals are calculated after creation if needed
            if ($order->wasChanged(['subtotal', 'discount', 'total'])) {
                $order->saveQuietly(); // Save without firing events again
            }
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
     * Get the staff member who created the order.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
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
     * Use query methods instead of loaded relationships to avoid N+1 issues.
     */
    public function calculateTotals(): void
    {
        // If order doesn't exist yet, skip calculation (will be done after creation)
        if (!$this->exists) {
            return;
        }

        // Calculate items subtotal using query (not loaded relationships)
        $itemsTotal = $this->items()->sum('total');
        
        // Calculate addons total using query
        $addonsTotal = $this->addons()->sum('order_addons.price');
        
        $this->subtotal = (float) $itemsTotal + (float) $addonsTotal;
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
        
        // Recalculate totals after adding addon
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Apply discount to the order.
     */
    public function applyDiscount(float $discount): void
    {
        $this->discount = $discount;
        $this->save(); // calculateTotals() will be called by the saving event
    }

    /**
     * Add a payment to the order.
     */
    public function addPayment(float $amount, string $method = 'cash'): Payment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method,
        ]);
        
        // Update paid amount by querying the database
        $this->paid_amount = (float) $this->payments()->sum('amount');
        $this->saveQuietly(); // Save without firing events
        
        return $payment;
    }

    /**
     * Calculate the balance (remaining amount to pay).
     */
    public function getBalanceAttribute(): float
    {
        // Use actual database values, not the ones that might be in memory
        $total = $this->getAttribute('total');
        $paid = $this->getAttribute('paid_amount');
        return (float) $total - (float) $paid;
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

    /**
     * Scope a query to only include orders from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('order_date', today());
    }

    /**
     * Scope a query to only include orders from a specific staff.
     */
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing orders.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include ready orders.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }
}