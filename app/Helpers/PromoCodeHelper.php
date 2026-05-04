<?php

namespace App\Helpers;

class PromoCodeHelper
{
    /**
     * Format discount display.
     */
    public static function formatDiscount(string $type, float $value): string
    {
        return $type === 'percentage' ? "{$value}%" : "₱{$value}";
    }

    /**
     * Format date range.
     */
    public static function formatDateRange(string $startDate, string $endDate): string
    {
        return self::formatDate($startDate) . ' - ' . self::formatDate($endDate);
    }

    /**
     * Format single date.
     */
    public static function formatDate(string $date): string
    {
        return \Carbon\Carbon::parse($date)->format('M j, Y');
    }

    /**
     * Get status badge HTML.
     */
    public static function getStatusBadge(bool $isActive): string
    {
        return $isActive
            ? '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>'
            : '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
    }

    /**
     * Get usage limit display.
     */
    public static function formatUsageLimit(?int $limit): string
    {
        return $limit ? (string) $limit : 'Unlimited';
    }

    /**
     * Validate discount value based on type.
     */
    public static function validateDiscountValue(string $type, float $value): bool
    {
        if ($type === 'percentage') {
            return $value >= 0 && $value <= 100;
        }

        return $value >= 0;
    }
}