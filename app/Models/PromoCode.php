<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $appends = [
        'remaining_uses',
    ];

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

    /**
     * Automatically deactivate promo codes that have passed their validity window.
     */
    public static function syncExpiredStatus(): int
    {
        return static::query()
            ->where('is_active', true)
            ->whereDate('end_date', '<', today()->toDateString())
            ->update(['is_active' => false]);
    }

    /**
     * Automatically deactivate promo codes that have reached their usage cap.
     */
    public static function syncUsageLimitStatus(): int
    {
        return static::query()
            ->where('is_active', true)
            ->whereNotNull('usage_limit')
            ->whereColumn('usage_count', '>=', 'usage_limit')
            ->update(['is_active' => false]);
    }

    /**
     * Sync all automatic inactive states for promo codes.
     */
    public static function syncAutomaticStatus(): int
    {
        return static::syncExpiredStatus() + static::syncUsageLimitStatus();
    }

    /**
     * Remaining uses before the promo reaches its usage cap.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        return max(0, (int) $this->usage_limit - $this->verifiedUsageCount());
    }

    /**
     * Count verified usage records for this promo code.
     */
    public function verifiedUsageCount(): int
    {
        return $this->relationLoaded('usages')
            ? $this->usages->count()
            : $this->usages()->count();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }
}
