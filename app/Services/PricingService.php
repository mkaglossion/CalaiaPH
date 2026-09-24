<?php

namespace App\Services;

use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakType;
use App\Models\PeakPeriod;
use App\Models\Holiday;
use App\Models\PricingConfig;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PricingService
{
    /**
     * Calculate total price for a booking
     * 
     * This is the CORE pricing method that implements your requirements:
     * 1. For each night (check-in inclusive, check-out exclusive):
     *    a. Determine Season Type (Priority: Super-Peak > Peak > Non-Peak)
     *    b. Determine Day Type (Holiday > Weekend > Weekday)
     *    c. Get rate from price_matrix[unit_type][season_type][day_type]
     * 2. Sum all nightly rates
     * 
     * @param string $unitType
     * @param string $checkInDate (Y-m-d format)
     * @param string $checkOutDate (Y-m-d format)
     * @return array
     */
    public function calculateBookingPrice(string $unitType, string $checkInDate, string $checkOutDate): array
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        if ($checkOut->lte($checkIn)) {
            return [
                'success' => false,
                'message' => 'Check-out date must be after check-in date.'
            ];
        }

        $pricingConfigs = PricingConfig::where('unit_type', $unitType)
            ->with(['peakType', 'dayType'])
            ->get();

        if ($pricingConfigs->isEmpty()) {
            return [
                'success' => false,
                'message' => "No pricing configuration found for unit type: {$unitType}"
            ];
        }

        // Build price lookup table
        $priceMatrix = $this->buildPriceMatrix($pricingConfigs);

        // Get helpers
        $holidayDates = $this->getHolidayDates($checkIn, $checkOut);
        $weekendDayIds = $this->getWeekendDayIds();
        $peakTypePriorities = $this->getPeakTypePriorities();

        // Calculate price per night
        $totalPrice = 0;
        $breakdown = [];
        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $nightDate = $current->format('Y-m-d');
            
            // Step A: Determine Season Type
            $seasonType = $this->determineSeasonType($current, $peakTypePriorities);
            
            // Step B: Determine Day Type
            $dayType = $this->determineDayType($current, $holidayDates, $weekendDayIds);
            
            // Step C: Get Rate
            $nightlyRate = $this->getRate($priceMatrix, $seasonType, $dayType);
            
            if ($nightlyRate === null) {
                return [
                    'success' => false,
                    'message' => "No rate found for {$unitType} | {$seasonType} | {$dayType} on {$nightDate}"
                ];
            }

            $totalPrice += $nightlyRate;
            $breakdown[] = [
                'date' => $nightDate,
                'day_of_week' => $current->format('l'),
                'season_type' => $seasonType,
                'day_type' => $dayType,
                'rate' => $nightlyRate
            ];

            $current->addDay();
        }

        return [
            'success' => true,
            'unit_type' => $unitType,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'nights' => count($breakdown),
            'total_price' => $totalPrice,
            'breakdown' => $breakdown
        ];
    }

    /**
     * Calculate price for a single night
     * 
     * @param string $unitType
     * @param string $date
     * @return array
     */
    public function calculateNightlyRate(string $unitType, string $date): array
    {
        $result = $this->calculateBookingPrice($unitType, $date, Carbon::parse($date)->addDay()->format('Y-m-d'));
        
        if ($result['success'] && !empty($result['breakdown'])) {
            return [
                'success' => true,
                'date' => $date,
                'unit_type' => $unitType,
                'rate' => $result['breakdown'][0]['rate'],
                'season_type' => $result['breakdown'][0]['season_type'],
                'day_type' => $result['breakdown'][0]['day_type']
            ];
        }

        return $result;
    }

    /**
     * Get the season type for a specific date
     * 
     * @param Carbon $date
     * @return string|null
     */
    public function getSeasonType(Carbon $date): ?string
    {
        $peakTypePriorities = $this->getPeakTypePriorities();
        return $this->determineSeasonType($date, $peakTypePriorities);
    }

    /**
     * Get the day type for a specific date
     * 
     * @param Carbon $date
     * @return string
     */
    public function getDayType(Carbon $date): string
    {
        $holidayDates = Holiday::whereDate('date', $date->format('Y-m-d'))
            ->pluck('date')
            ->map(function($d) { return Carbon::parse($d)->format('Y-m-d'); })
            ->toArray();
        
        $weekendDayIds = $this->getWeekendDayIds();
        
        return $this->determineDayType($date, $holidayDates, $weekendDayIds);
    }

    /**
     * Check if a date falls within a specific peak type
     * 
     * @param Carbon $date
     * @param string $peakTypeName
     * @return bool
     */
    public function isInPeakType(Carbon $date, string $peakTypeName): bool
    {
        $dateStr = $date->format('Y-m-d');
        
        $peakType = PeakType::where('name', $peakTypeName)->first();
        if (!$peakType) {
            return false;
        }

        return PeakPeriod::where('peak_type_id', $peakType->id)
            ->where(function($query) use ($dateStr) {
                $query->where('start_date', '<=', $dateStr)
                      ->where('end_date', '>=', $dateStr);
            })
            ->exists();
    }

    /**
     * Check if a date is a declared holiday
     * 
     * @param Carbon $date
     * @return bool
     */
    public function isHoliday(Carbon $date): bool
    {
        return Holiday::whereDate('date', $date->format('Y-m-d'))->exists();
    }

    /**
     * Check if a date is a weekend
     * 
     * @param Carbon $date
     * @return bool
     */
    public function isWeekend(Carbon $date): bool
    {
        $weekendDayIds = $this->getWeekendDayIds();
        return in_array((int) $date->dayOfWeek, $weekendDayIds);
    }

    /**
     * Get all peak periods for a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getPeakPeriodsInRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return PeakPeriod::where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where('end_date', '>=', $startDate->format('Y-m-d'));
                });
            })
            ->with('peakType')
            ->get();
    }

    /**
     * Get all holidays in a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getHolidaysInRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return Holiday::whereBetween('date', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ])->orderBy('date', 'asc')->get();
    }

    /**
     * Validate that new peak period doesn't cause issues
     * 
     * @param int $peakTypeId
     * @param string $startDate
     * @param string $endDate
     * @return array ['valid' => bool, 'message' => string, 'warnings' => array]
     */
    public function validatePeakPeriod(int $peakTypeId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Check for overlaps
        $overlaps = PeakPeriod::where(function($query) use ($start, $end) {
                $query->where(function($q) use ($start, $end) {
                    $q->where('start_date', '<=', $end->format('Y-m-d'))
                      ->where('end_date', '>=', $start->format('Y-m-d'));
                });
            })
            ->where('peak_type_id', '!=', $peakTypeId)
            ->with('peakType')
            ->get();

        $warnings = [];
        $hasHigherPriorityOverlap = false;

        $newPriority = PeakType::where('id', $peakTypeId)->value('priority');

        foreach ($overlaps as $overlap) {
            $overlapPriority = $overlap->peakType->priority ?? 0;
            
            if ($overlapPriority > $newPriority) {
                $hasHigherPriorityOverlap = true;
                $warnings[] = "Warning: This period overlaps with higher priority '{$overlap->peakType->name}' which will take precedence.";
            } else {
                $warnings[] = "Note: This period will take precedence over '{$overlap->peakType->name}'.";
            }
        }

        return [
            'valid' => true,
            'message' => $hasHigherPriorityOverlap 
                ? 'Period added but overlaps with higher priority periods.' 
                : 'Period added successfully.',
            'warnings' => $warnings
        ];
    }

    // ============ Private Helper Methods ============

    /**
     * Build price matrix from pricing configs
     * 
     * @param Collection $configs
     * @return array
     */
    private function buildPriceMatrix(Collection $configs): array
    {
        $matrix = [];
        
        foreach ($configs as $config) {
            $matrix[$config->peakType->name][$config->dayType->name] = $config->price;
        }
        
        return $matrix;
    }

    /**
     * Get holiday dates between check-in and check-out
     * 
     * @param Carbon $checkIn
     * @param Carbon $checkOut
     * @return array
     */
    private function getHolidayDates(Carbon $checkIn, Carbon $checkOut): array
    {
        return Holiday::whereBetween('date', [
            $checkIn->format('Y-m-d'),
            $checkOut->copy()->subDay()->format('Y-m-d')
        ])
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();
    }

    /**
     * Get weekend day IDs (0-6)
     * 
     * @return array
     */
    private function getWeekendDayIds(): array
    {
        $weekendId = DayType::where('name', 'Weekend')->value('id');
        return DayTypeDay::where('day_type_id', $weekendId)
            ->pluck('weekday')
            ->toArray();
    }

    /**
     * Get peak type priorities
     * 
     * @return array
     */
    private function getPeakTypePriorities(): array
    {
        return PeakType::pluck('priority', 'name')->toArray();
    }

    /**
     * Determine season type for a date
     * 
     * Priority: Super-Peak > Peak > Non-Peak
     * 
     * @param Carbon $date
     * @param array $priorities
     * @return string|null
     */
    private function determineSeasonType(Carbon $date, array $priorities): ?string
    {
        $dateStr = $date->format('Y-m-d');
        
        $matchingPeriods = PeakPeriod::where(function($query) use ($dateStr) {
                $query->where(function($q) use ($dateStr) {
                    $q->where('start_date', '<=', $dateStr)
                      ->where('end_date', '>=', $dateStr);
                });
            })
            ->with('peakType')
            ->get();

        if ($matchingPeriods->isEmpty()) {
            return null;
        }

        $highestPriority = -999;
        $selectedType = null;

        foreach ($matchingPeriods as $period) {
            $priority = $priorities[$period->peakType->name] ?? 0;
            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $selectedType = $period->peakType->name;
            }
        }

        return $selectedType;
    }

    /**
     * Determine day type for a date
     * 
     * Priority: Holiday > Weekend > Weekday
     * 
     * @param Carbon $date
     * @param array $holidayDates
     * @param array $weekendDayIds
     * @return string
     */
    private function determineDayType(Carbon $date, array $holidayDates, array $weekendDayIds): string
    {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = (int) $date->dayOfWeek;

        // 1. Check if it's a declared holiday
        if (in_array($dateStr, $holidayDates)) {
            return 'Holiday';
        }

        // 2. Check if it's a weekend
        if (in_array($dayOfWeek, $weekendDayIds)) {
            return 'Weekend';
        }

        // 3. Otherwise, it's a weekday
        return 'Weekday';
    }

    /**
     * Get rate from price matrix
     *
     * @param array $matrix
     * @param string $seasonType
     * @param string $dayType
     * @return float|null
     */
    private function getRate(array $matrix, ?string $seasonType, string $dayType)
    {
        if ($seasonType === null) {
            return null;
        }

        return $matrix[$seasonType][$dayType] ?? null;
    }

    private function getGlobalNightlyRate(\App\Models\Staycation $staycation, Carbon $date): ?float
    {
        $unitType = $this->resolveUnitTypeForStaycation($staycation);
        if ($unitType === null) {
            return null;
        }

        $nightlyRate = $this->calculateNightlyRate($unitType, $date->format('Y-m-d'));
        if (($nightlyRate['success'] ?? false) && isset($nightlyRate['rate']) && is_numeric($nightlyRate['rate'])) {
            return (float) $nightlyRate['rate'];
        }

        return null;
    }

    private function resolveUnitTypeForStaycation(\App\Models\Staycation $staycation): ?string
    {
        $type = strtolower(trim((string) ($staycation->type ?? '')));
        if (in_array($type, ['glamping', 'camping'], true)) {
            return $type;
        }

        $roomName = strtolower((string) ($staycation->name ?? ''));
        if (str_contains($roomName, 'glamping') || str_contains($roomName, 'dome') || str_contains($roomName, 'bell')) {
            return 'glamping';
        }

        if (str_contains($roomName, 'byot') || str_contains($roomName, 'camping') || str_contains($roomName, 'tent') || str_contains($roomName, 'car camping')) {
            return 'camping';
        }

        return null;
    }

    /**
     * Get staycation rate based on date, season, and day type
     *
     * Priority: Specific Date > Day-of-Week > Day Type (weekday/weekend) > Season > Fallback
     *
     * @param \App\Models\Staycation $staycation
     * @param \Carbon\Carbon $checkinDate
     * @param string $season
     * @return float
     */
    public function getStaycationRate(\App\Models\Staycation $staycation, \Carbon\Carbon $checkinDate, string $season): float
    {
        $priceConfigs = $staycation->price_configs;
        if (empty($priceConfigs)) {
            return $this->getGlobalNightlyRate($staycation, $checkinDate) ?? ($staycation->price ?? 0);
        }

        $configuredRate = $staycation->getConfiguredRateForDate($checkinDate, $season);
        if ($configuredRate !== null) {
            return $configuredRate;
        }

        $globalRate = $this->getGlobalNightlyRate($staycation, $checkinDate);
        if ($globalRate !== null) {
            return $globalRate;
        }

        return $staycation->price ?? 0;
    }

    /**
     * Calculate staycation price with multiple parameters
     * Alternative pricing method used by frontend/booking system
     * 
     * @param array $roomIds
     * @param string $checkinDate (Y-m-d format)
     * @param string $checkoutDate (Y-m-d format)
     * @param string $accommodationType
     * @param int $numberOfGuests
     * @param int $additionalAdults
     * @param int $numberOfPets
     * @param int|null $mattressQuantity
     * @return array
     */
    public function calculateStaycationPrice(
        array $roomIds,
        string $checkinDate,
        string $checkoutDate,
        string $accommodationType,
        int $numberOfGuests,
        int $additionalAdults = 0,
        int $numberOfPets = 0,
        ?int $mattressQuantity = null
    ): array {
        try {
            $checkin = Carbon::parse($checkinDate);
            $checkout = Carbon::parse($checkoutDate);

            if ($checkout->lte($checkin)) {
                throw new \Exception('Check-out date must be after check-in date.');
            }

            // Calculate base room rate
            $subtotal = 0;
            $nights = $checkout->diffInDays($checkin);

            // Get pricing from BookingSetting
            $baseRate = \App\Models\BookingSetting::where('key', 'weekday_mon_rate')
                ->where('category', 'pricing')
                ->value('value') ?? 3000;

            $subtotal = $baseRate * $nights;

            // Apply additional adult fees
            if ($additionalAdults > 0) {
                $additionalAdultFee = \App\Models\BookingSetting::where('key', 'additional_adult_fee')
                    ->where('category', 'pricing')
                    ->value('value') ?? 500;
                $subtotal += ($additionalAdultFee * $additionalAdults * $nights);
            }

            // Apply pet fees
            if ($numberOfPets > 0) {
                $petFee = \App\Models\BookingSetting::where('key', 'pet_price')
                    ->where('category', 'pricing')
                    ->value('value') ?? 1000;
                $subtotal += ($petFee * $numberOfPets * $nights);
            }

            // Apply mattress fees
            if ($mattressQuantity && $mattressQuantity > 0) {
                $mattressFee = \App\Models\BookingSetting::where('key', 'mattress_price_per_night')
                    ->where('category', 'pricing')
                    ->value('value') ?? 500;
                $subtotal += ($mattressFee * $mattressQuantity * $nights);
            }

            return [
                'success' => true,
                'subtotal' => $subtotal,
                'accommodation_type' => $accommodationType,
                'nights' => $nights,
                'checkin' => $checkinDate,
                'checkout' => $checkoutDate,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Pricing calculation failed: ' . $e->getMessage());
        }
    }
}
