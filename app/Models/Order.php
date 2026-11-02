<?php

namespace App\Models;

use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    /** In workflow order. "ready" = washed and waiting for pickup. */
    public const STATUSES = ['pending', 'processing', 'ready', 'completed', 'cancelled'];

    /** Statuses of orders still in the shop. */
    public const OPEN_STATUSES = ['pending', 'processing', 'ready'];

    /** An order is overdue once it has been pending/processing this many days. */
    public const OVERDUE_PENDING_DAYS = 1;
    public const OVERDUE_PROCESSING_DAYS = 3;

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
        'discount_percent',
        'total',
        'currency',
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
        'discount_percent' => 'decimal:2',
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

            // An order keeps the currency it was taken in
            if (empty($order->currency)) {
                $order->currency = app(Format::class)->currency;
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
     * discount = discount_percent of subtotal + add-ons when a percentage is set, otherwise
     *            the fixed amount; never more than subtotal + add-ons (reports sum it)
     * total    = subtotal + add-ons - discount
     */
    public function calculateTotals(): void
    {
        if (!$this->exists) {
            return;
        }

        // Round to the currency's smallest unit (whole yen and rupiah, centavos/cents elsewhere)
        $d = app(Format::class)->decimals($this->currency);
        $itemsTotal = (float) $this->items()->sum('total');
        $addonsTotal = (float) $this->addons()->sum('order_addons.price');
        $gross = round($itemsTotal + $addonsTotal, $d);

        $this->subtotal = round($itemsTotal, $d);
        if ($this->discount_percent !== null) {
            $this->discount = round($gross * (float) $this->discount_percent / 100, $d);
        }
        $this->discount = min(max(0, round((float) $this->discount, $d)), $gross);
        $this->total = round($gross - (float) $this->discount, $d);
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
        $this->discount_percent = null;   // a fixed amount replaces any percentage
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
            'ready' => 'Ready for pickup',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status)
        };
    }

    /**
     * Check if order can be edited: still open, and in the business currency
     * (an order taken before a currency change can't get today's prices).
     */
    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'ready'])
            && $this->getAttribute('currency') === app(Format::class)->currency;
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
     * Scope: orders waiting too long (see OVERDUE_* above). order_date is stored
     * as Y-m-d text, so string comparison works on SQLite and MySQL.
     */
    public function scopeOverdue($query)
    {
        $today = Carbon::today();

        return $query->where(function ($q) use ($today) {
            $q->where(fn ($q) => $q->where('status', 'pending')
                    ->where('order_date', '<=', $today->copy()->subDays(self::OVERDUE_PENDING_DAYS)->toDateString()))
              ->orWhere(fn ($q) => $q->where('status', 'processing')
                    ->where('order_date', '<=', $today->copy()->subDays(self::OVERDUE_PROCESSING_DAYS)->toDateString()));
        });
    }

    /**
     * Scope: orders with money still owed (cancelled orders owe nothing).
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', '!=', 'cancelled')->whereColumn('paid_amount', '<', 'total');
    }

    /**
     * Scope: orders in the business currency. Money totals only ever add up one
     * currency; orders taken before a currency change are reported separately.
     */
    public function scopeInBusinessCurrency($query)
    {
        return $query->where($query->qualifyColumn('currency'), app(Format::class)->currency);
    }

    /**
     * Orders of $query in other currencies, per currency: [currency, orders, total].
     * Shown as a note under totals that leave them out.
     */
    public static function otherCurrencyTotals($query)
    {
        return (clone $query)
            ->where($query->qualifyColumn('currency'), '!=', app(Format::class)->currency)
            ->selectRaw('currency, COUNT(*) as orders, COALESCE(SUM(total), 0) as total')
            ->groupBy('currency')
            ->get();
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
