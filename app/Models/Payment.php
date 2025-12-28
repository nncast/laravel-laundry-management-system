<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get formatted amount attribute
     */
    public function getFormattedAmountAttribute()
    {
        return '₱' . number_format($this->amount, 2);
    }

    /**
     * Get payment date from created_at
     */
    public function getPaymentDateAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Get payment date in human readable format
     */
    public function getPaymentDateFormattedAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }
}