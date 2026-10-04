<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

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
                $order->order_date = now()->toDateString();
            }
        });
    }

    /**
     * Store dates as plain Y-m-d so that date comparisons behave the same on
     * SQLite (which stores text) and MySQL (which has a real DATE column).
     */
    public function setOrderDateAttribute($value): void
    {
        $this->attributes['order_date'] = $value ? Carbon::parse($value)->toDateString() : null;
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
     * Alias of items(), kept for existing callers.
     */
    public function orderItems(): HasMany
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
     * Recalculate subtotal/total from the items and add-ons stored in the database.
     *
     * subtotal = sum of item totals (services only)
     * total    = subtotal + add-ons - discount (never negative)
     */
    public function calculateTotals(): void
    {
        if (!$this->exists) {
            return;
        }

        $itemsTotal = (float) $this->items()->sum('total');
        $addonsTotal = (float) $this->addons()->sum('order_addons.price');

        $this->subtotal = round($itemsTotal, 2);
        $this->total = max(0, round($itemsTotal + $addonsTotal - (float) $this->discount, 2));
    }

    /**
     * Recalculate the paid amount from the payment records.
     */
    public function syncPaidAmount(): void
    {
        $this->paid_amount = round((float) $this->payments()->sum('amount'), 2);
    }

    /**
     * Recalculate totals and paid amount, then persist.
     */
    public function updateTotals(): void
    {
        $this->calculateTotals();
        $this->syncPaidAmount();
        $this->save();
    }

    /**
     * Add a service to the order.
     */
    public function addService(Service $service, int $quantity = 1): OrderItem
    {
        $existingItem = $this->items()
            ->where('service_id', $service->id)
            ->first();

        if ($existingItem) {
            $existingItem->qty += $quantity;
            $existingItem->save();
            $this->updateTotals();
            return $existingItem;
        }

        $item = $this->items()->create([
            'service_id' => $service->id,
            'price' => $service->price,
            'rate' => 1,
            'qty' => $quantity,
        ]);

        $this->updateTotals();

        return $item;
    }

    /**
     * Add an addon to the order.
     */
    public function addAddon(Addon $addon): void
    {
        $this->addons()->syncWithoutDetaching([
            $addon->id => ['price' => $addon->price],
        ]);

        $this->updateTotals();
    }

    /**
     * Apply discount to the order.
     */
    public function applyDiscount(float $discount): void
    {
        $this->discount = max(0, $discount);
        $this->updateTotals();
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

        $this->syncPaidAmount();
        $this->save();

        return $payment;
    }

    /**
     * Calculate the balance (remaining amount to pay).
     */
    public function getBalanceAttribute(): float
    {
        return round((float) $this->getAttribute('total') - (float) $this->getAttribute('paid_amount'), 2);
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
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status)
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
     * Scope: orders whose order_date falls within [start, end] (inclusive, Y-m-d).
     *
     * Uses a half-open range on the raw column so it can use the index and also
     * matches rows stored as "Y-m-d 00:00:00" by older versions of the app.
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        $start = Carbon::parse($start)->toDateString();
        $endExclusive = Carbon::parse($end)->addDay()->toDateString();

        return $query->where('order_date', '>=', $start)
                     ->where('order_date', '<', $endExclusive);
    }

    /**
     * Scope: orders on a single day.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->betweenDates($date, $date);
    }

    /**
     * Scope a query to only include orders from today.
     */
    public function scopeToday($query)
    {
        return $query->onDate(today());
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
}
