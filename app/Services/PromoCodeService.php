<?php

namespace App\Services;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PromoCodeService
{
    /**
     * Get paginated promo codes with filters.
     */
    public function getPaginatedPromoCodes(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PromoCode::query();

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new promo code.
     */
    public function createPromoCode(array $data): PromoCode
    {
        return PromoCode::create($data);
    }

    /**
     * Update an existing promo code.
     */
    public function updatePromoCode(PromoCode $promoCode, array $data): PromoCode
    {
        $promoCode->update($data);
        return $promoCode->fresh();
    }

    /**
     * Delete a promo code.
     */
    public function deletePromoCode(PromoCode $promoCode): bool
    {
        return $promoCode->delete();
    }

    /**
     * Toggle the active status of a promo code.
     */
    public function togglePromoCodeStatus(PromoCode $promoCode): PromoCode
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);
        return $promoCode->fresh();
    }

    /**
     * Validate and apply promo code discount.
     */
    public function validateAndCalculateDiscount(string $code, float $bookingAmount): ?array
    {
        PromoCode::syncAutomaticStatus();

        $promoCode = PromoCode::active()
            ->where('code', $code)
            ->validForAmount($bookingAmount)
            ->first();

        if (!$promoCode) {
            return null;
        }

        // Check usage limit if set
        if ($promoCode->usage_limit && $this->getUsageCount($promoCode) >= $promoCode->usage_limit) {
            return null;
        }

        $discount = $this->calculateDiscount($promoCode, $bookingAmount);

        return [
            'promo_code' => $promoCode,
            'discount_amount' => $discount,
            'final_amount' => $bookingAmount - $discount,
        ];
    }

    /**
     * Calculate discount amount based on promo code type.
     */
    protected function calculateDiscount(PromoCode $promoCode, float $amount): float
    {
        if ($promoCode->discount_type === 'percentage') {
            return ($amount * $promoCode->discount_value) / 100;
        }

        return min($promoCode->discount_value, $amount);
    }

    /**
     * Get usage count for a promo code (placeholder for future implementation).
     */
    protected function getUsageCount(PromoCode $promoCode): int
    {
        return $promoCode->verifiedUsageCount();
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (!empty($filters['discount_type'])) {
            $query->where('discount_type', $filters['discount_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }
    }
}
