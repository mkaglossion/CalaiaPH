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
        'discount_amount',
        'discounted_total',
        'vat_amount',
        'final_total',
        'delivery_option',
        'delivery_address',
        'pickup_date',
        'payment_method',
        'transaction_id',
        'paymongo_checkout_id',
        'payment_status',
        'paid_at',
        'discount_type',
        'discount_id_number',
        'id_image_path',
        'discount_status',
        'rejection_reason',
        'verified_at',
        'claimed_at',
        'verified_by',
        'order_status',
        'special_requests',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'original_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discounted_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'final_total' => 'decimal:2',
        'pickup_date' => 'date',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Scope for pending discount verifications
     */
    public function scopePendingDiscount($query)
    {
        return $query->where('discount_type', '!=', 'none')
                     ->where('discount_status', 'pending');
    }

    /**
     * Scope for orders needing verification (same logic as bookings)
     */
    public function scopeNeedsVerification($query)
    {
        return $query->where('discount_type', '!=', 'none')
                     ->whereIn('discount_status', ['pending', 'rejected']);
    }

    /**
     * Check if order has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount_type !== 'none' && $this->discount_type !== null;
    }

    /**
     * Get discount label
     */
    public function getDiscountLabelAttribute(): string
    {
        return match($this->discount_type) {
            'senior' => 'Senior Citizen',
            'pwd' => 'PWD',
            default => 'None',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->discount_status) {
            'verified' => 'green',
            'claimed' => 'blue',
            'rejected' => 'red',
            default => 'yellow',
        };
    }
}
