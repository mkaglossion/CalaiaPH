<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'min_booking_amount',
        'is_active',
        'description',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_booking_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active promo codes (not expired and enabled).
     */
    public function scopeActive($query)
    {
        $today = today()->toDateString();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    /**
     * Scope for promo codes valid for a specific amount.
     */
    public function scopeValidForAmount($query, $amount)
    {
        return $query->where(function ($q) use ($amount) {
            $q->whereNull('min_booking_amount')
              ->orWhere('min_booking_amount', '<=', $amount);
        });
    }
}
