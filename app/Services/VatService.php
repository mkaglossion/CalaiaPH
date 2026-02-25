<?php

namespace App\Services;

/**
 * VAT Service for Philippine Tax Calculations
 * 
 * Implements Value Added Tax (VAT) calculations aligned with Philippine tax regulations.
 * Current VAT rate: 12% (per TRAIN Law)
 * 
 * @see https://www.bir.gov.ph/index.php/tax-information/vat.html
 */
class VatService
{
    /**
     * Philippine VAT rate (12%)
     */
    const VAT_RATE = 0.12;

    /**
     * VAT rate as percentage for display
     */
    const VAT_RATE_PERCENTAGE = 12;

    /**
     * Calculate VAT from VAT-inclusive price
     * Formula: VAT = Price × (Rate / (1 + Rate))
     * Example: VAT from ₱5,600 = ₱5,600 × (0.12/1.12) = ₱600
     * 
     * @param float $inclusivePrice Price that already includes VAT
     * @return float VAT amount
     */
    public function extractVatFromInclusive(float $inclusivePrice): float
    {
        return round($inclusivePrice * (self::VAT_RATE / (1 + self::VAT_RATE)), 2);
    }

    /**
     * Calculate VAT from VAT-exclusive price
     * Formula: VAT = Price × Rate
     * Example: VAT from ₱5,000 = ₱5,000 × 0.12 = ₱600
     * 
     * @param float $exclusivePrice Price without VAT
     * @return float VAT amount
     */
    public function calculateVatFromExclusive(float $exclusivePrice): float
    {
        return round($exclusivePrice * self::VAT_RATE, 2);
    }

    /**
     * Get net price (price without VAT) from VAT-inclusive price
     * Formula: Net = Price / (1 + Rate)
     * Example: Net from ₱5,600 = ₱5,600 / 1.12 = ₱5,000
     * 
     * @param float $inclusivePrice Price that already includes VAT
     * @return float Net price without VAT
     */
    public function getNetPrice(float $inclusivePrice): float
    {
        return round($inclusivePrice / (1 + self::VAT_RATE), 2);
    }

    /**
     * Add VAT to exclusive price
     * Formula: Gross = Price × (1 + Rate)
     * Example: ₱5,000 + 12% VAT = ₱5,600
     * 
     * @param float $exclusivePrice Price without VAT
     * @return float Price with VAT included
     */
    public function addVat(float $exclusivePrice): float
    {
        return round($exclusivePrice * (1 + self::VAT_RATE), 2);
    }

    /**
     * Generate complete VAT breakdown for receipts/invoices
     * 
     * @param float $amount The price amount
     * @param bool $isVatInclusive Whether the amount already includes VAT (default: true for B2C)
     * @return array Detailed breakdown with net, VAT, and gross amounts
     */
    public function generateBreakdown(float $amount, bool $isVatInclusive = true): array
    {
        if ($isVatInclusive) {
            // Amount already includes VAT - extract it
            $vat = $this->extractVatFromInclusive($amount);
            $netPrice = round($amount - $vat, 2);
            $grossAmount = $amount;
        } else {
            // Amount is net - add VAT
            $netPrice = $amount;
            $vat = $this->calculateVatFromExclusive($amount);
            $grossAmount = $this->addVat($amount);
        }

        return [
            'gross_amount' => $grossAmount,      // Total amount including VAT
            'net_amount' => $netPrice,           // Amount before VAT
            'vat_amount' => $vat,                // VAT amount
            'vat_rate' => self::VAT_RATE,        // 0.12
            'vat_rate_display' => self::VAT_RATE_PERCENTAGE, // 12
            'vat_label' => self::VAT_RATE_PERCENTAGE . '% VAT',
            'is_vat_inclusive' => $isVatInclusive,
        ];
    }

    /**
     * Generate VAT breakdown for multiple items
     * 
     * @param array $items Array of items with 'amount' key
     * @param bool $isVatInclusive Whether amounts include VAT
     * @return array Combined breakdown for all items
     */
    public function generateBreakdownForItems(array $items, bool $isVatInclusive = true): array
    {
        $totalAmount = 0;
        
        foreach ($items as $item) {
            $totalAmount += (float) ($item['amount'] ?? $item['price'] ?? 0);
        }

        return $this->generateBreakdown($totalAmount, $isVatInclusive);
    }

    /**
     * Calculate VAT for booking components
     * 
     * @param float $accommodationTotal Accommodation cost
     * @param float $foodTotal Food and beverages cost
     * @param float $activitiesTotal Activities cost
     * @param bool $isVatInclusive Whether amounts include VAT
     * @return array Detailed breakdown by component
     */
    public function calculateBookingVat(
        float $accommodationTotal,
        float $foodTotal = 0,
        float $activitiesTotal = 0,
        bool $isVatInclusive = true
    ): array {
        $subtotal = $accommodationTotal + $foodTotal + $activitiesTotal;
        $breakdown = $this->generateBreakdown($subtotal, $isVatInclusive);

        // Add component breakdowns
        $breakdown['components'] = [
            'accommodation' => $this->generateBreakdown($accommodationTotal, $isVatInclusive),
            'food' => $this->generateBreakdown($foodTotal, $isVatInclusive),
            'activities' => $this->generateBreakdown($activitiesTotal, $isVatInclusive),
        ];

        return $breakdown;
    }

    /**
     * Format VAT amount for display
     * 
     * @param float $amount
     * @return string Formatted amount with peso sign
     */
    public function formatAmount(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }

    /**
     * Get VAT rate information
     * 
     * @return array VAT rate details
     */
    public function getVatInfo(): array
    {
        return [
            'rate' => self::VAT_RATE,
            'rate_percentage' => self::VAT_RATE_PERCENTAGE,
            'label' => self::VAT_RATE_PERCENTAGE . '% VAT',
            'description' => 'Value Added Tax (VAT) as per Philippine TRAIN Law',
            'effective_date' => '2018-01-01', // TRAIN Law implementation
        ];
    }

    /**
     * Check if amount is VAT-exempt
     * Some transactions are VAT-exempt under Philippine tax law
     * 
     * @param string $type Transaction type
     * @return bool
     */
    public function isVatExempt(string $type): bool
    {
        $exemptTypes = [
            'export',           // Export sales
            'foreign_currency', // Foreign currency transactions
            'senior_citizen',   // Senior citizen discounts (portion)
            'pwd',             // PWD discounts (portion)
        ];

        return in_array($type, $exemptTypes);
    }
}
