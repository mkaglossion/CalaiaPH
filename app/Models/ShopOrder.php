<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'cart_items',
        'original_total',
        'vat_amount',
        'final_total',
        'delivery_option',
        'delivery_address',
        'pickup_date',
        'payment_method',
        'transaction_id',
        'payment_status',
        'order_status',
        'special_requests',
        'paymongo_checkout_id',
        'paid_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'pickup_date' => 'date',
        'paid_at' => 'datetime',
        'original_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'final_total' => 'decimal:2',
    ];

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';

    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_READY = 'ready';
    // Backward-compatible alias used by existing code paths.
    const ORDER_STATUS_CONFIRMED = self::ORDER_STATUS_READY;
    const ORDER_STATUS_PROCESSING = 'processing';
    const ORDER_STATUS_COMPLETED = 'completed';
    const ORDER_STATUS_CANCELLED = 'cancelled';

    const DELIVERY_OPTION_PICKUP = 'pickup';
    const DELIVERY_OPTION_DELIVERY = 'delivery';

    /**
     * Get formatted final total for display.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format((float) $this->final_total, 2);
    }

    /**
     * Get formatted VAT amount for display.
     */
    public function getFormattedVatAttribute(): string
    {
        return '₱' . number_format((float) $this->vat_amount, 2);
    }

    /**
     * Get formatted original total for display.
     */
    public function getFormattedOriginalTotalAttribute(): string
    {
        return '₱' . number_format((float) $this->original_total, 2);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if order is confirmed/completed.
     */
    public function isConfirmed(): bool
    {
        return in_array($this->order_status, [self::ORDER_STATUS_READY, self::ORDER_STATUS_COMPLETED], true);
    }

    /**
     * Get the total item count in the order.
     */
    public function getItemCountAttribute(): int
    {
        return collect($this->cart_items)->sum('quantity');
    }
}
