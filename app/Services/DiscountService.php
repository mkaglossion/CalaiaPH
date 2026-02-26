<?php

namespace App\Services;

/**
 * Discount Service for Philippine Senior Citizen and PWD Discounts
 * 
 * Implements discount calculations following Philippine laws:
 * - RA 9994 (Senior Citizens Act of 2010)
 * - RA 10754 (Magna Carta for Persons with Disabilities)
 * 
 * Current Discount Rate: 20% off on goods and services
 * VAT Exemption: Discounted amount is VAT-exempt
 */
class DiscountService
{
    /**
     * Discount rate for Senior Citizens and PWDs (20%)
     */
    const SENIOR_PWD_DISCOUNT = 0.20;

    /**
     * Discount rate as percentage for display
     */
    const DISCOUNT_RATE_PERCENTAGE = 20;

    /**
     * Valid discount types
     */
    const VALID_DISCOUNT_TYPES = ['senior', 'pwd'];

    /**
     * Apply discount to a given amount
     *
     * @param float $amount Original amount before discount
     * @param string $discountType Type of discount: 'senior' or 'pwd'
     * @return array Discount breakdown
     */
    public function applyDiscount(float $amount, string $discountType = 'none'): array
    {
        $discountType = strtolower($discountType);
        
        if (!in_array($discountType, self::VALID_DISCOUNT_TYPES)) {
            return [
                'original_amount' => $amount,
                'discount_type' => 'none',
                'discount_rate' => 0,
                'discount_amount' => 0,
                'discounted_amount' => $amount,
                'is_vat_exempt' => false,
                'total_payable' => $amount,
            ];
        }

        // Apply 20% discount
        $discountAmount = $amount * self::SENIOR_PWD_DISCOUNT;
        $discountedAmount = $amount - $discountAmount;

        // The discounted amount is VAT-exempt per Philippine tax laws
        $isVatExempt = true;
        $totalPayable = $discountedAmount;

        return [
            'original_amount' => $amount,
            'discount_type' => $discountType,
            'discount_rate' => self::DISCOUNT_RATE_PERCENTAGE,
            'discount_amount' => $discountAmount,
            'discounted_amount' => $discountedAmount,
            'is_vat_exempt' => $isVatExempt,
            'total_payable' => $totalPayable,
        ];
    }

    /**
     * Apply discount to multiple items with breakdown by component
     *
     * @param array $items Array of items with 'amount' key
     * @param string $discountType Type of discount: 'senior' or 'pwd'
     * @return array Combined discount breakdown for all items
     */
    public function applyDiscountToItems(array $items, string $discountType = 'none'): array
    {
        $totalAmount = 0;
        $itemBreakdown = [];

        foreach ($items as $index => $item) {
            $itemAmount = (float) ($item['amount'] ?? $item['price'] ?? 0);
            $totalAmount += $itemAmount;
            
            $itemBreakdown[$index] = [
                'item' => $item,
                'breakdown' => $this->applyDiscount($itemAmount, $discountType),
            ];
        }

        $totalBreakdown = $this->applyDiscount($totalAmount, $discountType);

        return [
            'total_breakdown' => $totalBreakdown,
            'items_breakdown' => $itemBreakdown,
        ];
    }

    /**
     * Calculate discount for booking components
     *
     * @param float $accommodationTotal Accommodation cost
     * @param float $foodTotal Food and beverages cost
     * @param float $activitiesTotal Activities cost
     * @param string $discountType Type of discount: 'senior' or 'pwd'
     * @return array Detailed breakdown by component
     */
    public function calculateBookingDiscount(
        float $accommodationTotal,
        float $foodTotal = 0,
        float $activitiesTotal = 0,
        string $discountType = 'none'
    ): array {
        $subtotal = $accommodationTotal + $foodTotal + $activitiesTotal;
        $totalBreakdown = $this->applyDiscount($subtotal, $discountType);

        // Add component breakdowns
        $totalBreakdown['components'] = [
            'accommodation' => $this->applyDiscount($accommodationTotal, $discountType),
            'food' => $this->applyDiscount($foodTotal, $discountType),
            'activities' => $this->applyDiscount($activitiesTotal, $discountType),
        ];

        return $totalBreakdown;
    }

    /**
     * Validate discount type
     *
     * @param string $discountType Type of discount
     * @return bool Whether the discount type is valid
     */
    public function isValidDiscountType(string $discountType): bool
    {
        return in_array(strtolower($discountType), self::VALID_DISCOUNT_TYPES);
    }

    /**
     * Get discount label for display
     *
     * @param string $discountType Type of discount
     * @return string Display label for the discount
     */
    public function getDiscountLabel(string $discountType): string
    {
        $discountType = strtolower($discountType);
        
        switch ($discountType) {
            case 'senior':
                return 'Senior Citizen Discount (' . self::DISCOUNT_RATE_PERCENTAGE . '%)';
            case 'pwd':
                return 'PWD Discount (' . self::DISCOUNT_RATE_PERCENTAGE . '%)';
            default:
                return 'No Discount';
        }
    }

    /**
     * Format amount for display
     *
     * @param float $amount Amount to format
     * @return string Formatted amount with peso sign
     */
    public function formatAmount(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }

    /**
     * Get discount information
     *
     * @return array Discount rate details
     */
    public function getDiscountInfo(): array
    {
        return [
            'rate' => self::SENIOR_PWD_DISCOUNT,
            'rate_percentage' => self::DISCOUNT_RATE_PERCENTAGE,
            'valid_types' => self::VALID_DISCOUNT_TYPES,
            'description' => '20% discount for Senior Citizens and PWDs as per RA 9994 and RA 10754',
            'vat_exemption' => true,
        ];
    }

    /**
     * Calculate final amount including both discount and VAT
     *
     * @param float $amount Original amount
     * @param string $discountType Type of discount
     * @return array Complete calculation with both discount and VAT
     */
    public function calculateFinalAmount(float $amount, string $discountType = 'none'): array
    {
        $discountBreakdown = $this->applyDiscount($amount, $discountType);
        $vatService = app(VatService::class);

        if ($discountBreakdown['is_vat_exempt']) {
            // VAT-exempt: no VAT calculation needed
            return [
                'original_amount' => $discountBreakdown['original_amount'],
                'discount_type' => $discountBreakdown['discount_type'],
                'discount_rate' => $discountBreakdown['discount_rate'],
                'discount_amount' => $discountBreakdown['discount_amount'],
                'discounted_amount' => $discountBreakdown['discounted_amount'],
                'vat_amount' => 0,
                'total_payable' => $discountBreakdown['total_payable'],
                'is_vat_exempt' => true,
            ];
        }

        // Calculate VAT on the discounted amount
        $vatBreakdown = $vatService->generateBreakdown($discountBreakdown['discounted_amount'], true);

        return [
            'original_amount' => $discountBreakdown['original_amount'],
            'discount_type' => $discountBreakdown['discount_type'],
            'discount_rate' => $discountBreakdown['discount_rate'],
            'discount_amount' => $discountBreakdown['discount_amount'],
            'discounted_amount' => $discountBreakdown['discounted_amount'],
            'vat_amount' => $vatBreakdown['vat_amount'],
            'total_payable' => $vatBreakdown['gross_amount'],
            'is_vat_exempt' => false,
        ];
    }
}
