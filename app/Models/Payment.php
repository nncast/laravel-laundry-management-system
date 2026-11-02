<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'received_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /** Every payment remembers who took it: the cashier on its receipt. */
    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->received_by ??= session('staff.id');
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** The staff member who took the payment. */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    /** The number on its receipt, e.g. R-000123. */
    public function getReceiptNumberAttribute(): string
    {
        return 'R-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
