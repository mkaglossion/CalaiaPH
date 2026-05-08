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
     * Default VAT rate (0% initially)
     * Used when admin settings don't specify a rate
     */
    const DEFAULT_VAT_RATE = 0.0;
    const DEFAULT_VAT_RATE_PERCENTAGE = 0;

    /**
     * @deprecated Use getDynamicVatRate() or getCurrentVatRate() instead
     * Kept for backward compatibility
     */
    const VAT_RATE = 0.0;
    const VAT_RATE_PERCENTAGE = 0;

    /**
     * Get current VAT rate from admin settings (0-1 format)
     * Defaults to 12% if not configured
     * 
     * @return float VAT rate as decimal (e.g., 0.12 for 12%)
     */
    public function getCurrentVatRate(): float
    {
        $settings = cache()->get('admin.settings', []);
        $vatPercentage = $settings['vat_rate'] ?? self::DEFAULT_VAT_RATE_PERCENTAGE;
        return $vatPercentage / 100;
    }

    /**
     * Get current VAT rate as percentage (0-100 format)
     * 
     * @return float VAT rate as percentage (e.g., 12 for 12%)
     */
    public function getCurrentVatRatePercentage(): float
    {
        $settings = cache()->get('admin.settings', []);
        return $settings['vat_rate'] ?? self::DEFAULT_VAT_RATE_PERCENTAGE;
    }

    /**
     * Calculate VAT from VAT-inclusive price
     * Formula: VAT = Price × (Rate / (1 + Rate))
     * Example: VAT from ₱5,600 = ₱5,600 × (0.12/1.12) = ₱600
     * 
     * @param float $inclusivePrice Price that already includes VAT
     * @param float|null $vatRate Optional VAT rate (0-1 format). Uses current rate if null
     * @return float VAT amount
     */
    public function extractVatFromInclusive(float $inclusivePrice, ?float $vatRate = null): float
    {
        $rate = $vatRate ?? $this->getCurrentVatRate();
        return round($inclusivePrice * ($rate / (1 + $rate)), 2);
    }

    /**
     * Calculate VAT from VAT-exclusive price
     * Formula: VAT = Price × Rate
     * Example: VAT from ₱5,000 = ₱5,000 × 0.12 = ₱600
     * 
     * @param float $exclusivePrice Price without VAT
     * @param float|null $vatRate Optional VAT rate (0-1 format). Uses current rate if null
     * @return float VAT amount
     */
    public function calculateVatFromExclusive(float $exclusivePrice, ?float $vatRate = null): float
    {
        $rate = $vatRate ?? $this->getCurrentVatRate();
        return round($exclusivePrice * $rate, 2);
    }

    /**
     * Get net price (price without VAT) from VAT-inclusive price
     * Formula: Net = Price / (1 + Rate)
     * Example: Net from ₱5,600 = ₱5,600 / 1.12 = ₱5,000
     * 
     * @param float $inclusivePrice Price that already includes VAT
     * @param float|null $vatRate Optional VAT rate (0-1 format). Uses current rate if null
     * @return float Net price without VAT
     */
    public function getNetPrice(float $inclusivePrice, ?float $vatRate = null): float
    {
        $rate = $vatRate ?? $this->getCurrentVatRate();
        return round($inclusivePrice / (1 + $rate), 2);
    }

    /**
     * Add VAT to exclusive price
     * Formula: Gross = Price × (1 + Rate)
     * Example: ₱5,000 + 12% VAT = ₱5,600
     * 
     * @param float $exclusivePrice Price without VAT
     * @param float|null $vatRate Optional VAT rate (0-1 format). Uses current rate if null
     * @return float Price with VAT included
     */
    public function addVat(float $exclusivePrice, ?float $vatRate = null): float
    {
        $rate = $vatRate ?? $this->getCurrentVatRate();
        return round($exclusivePrice * (1 + $rate), 2);
    }

    /**
     * Generate complete VAT breakdown for receipts/invoices
     * 
     * @param float $amount The price amount
     * @param bool $isVatInclusive Whether the amount already includes VAT (default: true for B2C)
     * @param float|null $vatRate Optional VAT rate (0-1 format). Uses current rate if null
     * @return array Detailed breakdown with net, VAT, and gross amounts
     */
    public function generateBreakdown(float $amount, bool $isVatInclusive = true, ?float $vatRate = null): array
    {
        $rate = $vatRate ?? $this->getCurrentVatRate();
        $ratePercentage = $this->getCurrentVatRatePercentage();

        if ($isVatInclusive) {
            // Amount already includes VAT - extract it
            $vat = $this->extractVatFromInclusive($amount, $rate);
            $netPrice = round($amount - $vat, 2);
            $grossAmount = $amount;
        } else {
            // Amount is net - add VAT
            $netPrice = $amount;
            $vat = $this->calculateVatFromExclusive($amount, $rate);
            $grossAmount = $this->addVat($amount, $rate);
        }

        return [
            'gross_amount' => $grossAmount,      // Total amount including VAT
            'net_amount' => $netPrice,           // Amount before VAT
            'vat_amount' => $vat,                // VAT amount
            'vat_rate' => $rate,                 // 0.12 or dynamic
            'vat_rate_display' => $ratePercentage, // 12 or dynamic
            'vat_label' => $ratePercentage . '% VAT',
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
     * Get VAT rate information (dynamic from admin settings)
     * 
     * @return array VAT rate details
     */
    public function getVatInfo(): array
    {
        $ratePercentage = $this->getCurrentVatRatePercentage();
        $rate = $this->getCurrentVatRate();
        
        return [
            'rate' => $rate,
            'rate_percentage' => $ratePercentage,
            'label' => $ratePercentage . '% VAT',
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
        ];

        return in_array($type, $exemptTypes);
    }
}
