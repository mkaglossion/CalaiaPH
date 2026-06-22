<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class Staycation extends Model
{
    protected $fillable = ['name', 'days', 'type', 'base_quantity', 'capacity', 'max_guest_capacity', 'included_guest_capacity', 'adults', 'children', 'image', 'price_configs', 'price', 'sort_order'];

    protected $casts = [
        'price_configs' => 'array',
        'max_guest_capacity' => 'integer',
        'included_guest_capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($staycation) {
            if (!Schema::hasColumn($staycation->getTable(), 'sort_order') || !empty($staycation->sort_order)) {
                return;
            }

            $staycation->sort_order = ((int) static::max('sort_order')) + 1;
        });

        static::updated(function ($staycation) {
            // If quantity changed, sync inventory
            if ($staycation->wasChanged('base_quantity')) {
                // Run sync for this staycation
                Artisan::call('staycation:sync-inventory', [
                    '--days' => 90,
                    '--staycation' => $staycation->id
                ]);
            }
        });
    }

    public function scopeOrdered($query)
    {
        if (Schema::hasColumn($this->getTable(), 'sort_order')) {
            return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }

        return $query->orderBy('id', 'asc');
    }

    public function texts()
    {
        return $this->hasMany(StaycationText::class);
    }

    public function swiperImages()
    {
        return $this->hasMany(StaycationSwiperImage::class);
    }

    public function inventories()
    {
        return $this->hasMany(StaycationInventory::class);
    }

    // Accommodations via variants
    public function staycationAccommodations()
    {
        return $this->belongsToMany(
            StaycationAccommodation::class,
            'accommodation_variants',
            'staycation_id',
            'staycation_accommodation_id'
        );
    }

    // Accessor for today's available quantity
    public function getTodayAvailableAttribute()
    {
        $today = now()->toDateString();
        $inventory = StaycationInventory::where('staycation_id', $this->id)
            ->where('date', $today)
            ->first();
        return $inventory ? $inventory->available_quantity : 0;
    }

    /**
     * Resolve the configured room rate for a given date and season.
     * Returns null when no room-specific config matches that date.
     */
    public function getConfiguredRateForDate($date, string $seasonKey): ?float
    {
        $matchingConfig = $this->getMatchingPriceConfigForDate($date);
        if (!$matchingConfig) {
            return null;
        }

        $rates = $matchingConfig['rates'] ?? [];
        $rate = $rates[$seasonKey] ?? null;

        return is_numeric($rate) ? (float) $rate : null;
    }

    /**
     * Find the first room pricing config that applies to the given date.
     *
     * Priority:
     * 1. Specific date(s)
     * 2. Date range
     * 3. Day-of-week
     */
    public function getMatchingPriceConfigForDate($date): ?array
    {
        $configs = $this->price_configs ?? [];
        if (!is_array($configs) || empty($configs)) {
            return null;
        }

        $carbonDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $carbonDate->format('Y-m-d');
        $dayOfWeek = $carbonDate->format('D');

        foreach (['date', 'range', 'day'] as $type) {
            foreach ($configs as $config) {
                if (!is_array($config) || $this->normalizePriceConfigType($config) !== $type) {
                    continue;
                }

                if ($type === 'range' && $this->matchesRangeConfig($config, $dateStr, $dayOfWeek)) {
                    return $config;
                }

                if ($type === 'date' && $this->matchesDateConfig($config, $dateStr)) {
                    return $config;
                }

                if ($type === 'day' && $this->matchesDayConfig($config, $dayOfWeek)) {
                    return $config;
                }
            }
        }

        return null;
    }

    private function normalizePriceConfigType(array $config): string
    {
        $type = strtolower(trim((string) ($config['type'] ?? '')));

        if ($type === 'range' || isset($config['startDate']) || isset($config['start_date']) || isset($config['endDate']) || isset($config['end_date'])) {
            return 'range';
        }

        $rawDates = $config['dates'] ?? null;
        $multipleDates = $config['multipleDates'] ?? null;

        if (
            $type === 'date'
            || (!empty($rawDates) && $rawDates !== [])
            || (!empty($multipleDates) && $multipleDates !== false)
        ) {
            return 'date';
        }

        return 'day';
    }

    private function matchesRangeConfig(array $config, string $dateStr, string $dayOfWeek): bool
    {
        $start = $config['startDate'] ?? $config['start_date'] ?? null;
        $end = $config['endDate'] ?? $config['end_date'] ?? null;

        if (empty($start) || empty($end)) {
            return false;
        }

        try {
            $current = Carbon::parse($dateStr);
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();

            if (!$current->betweenIncluded($startDate, $endDate)) {
                return false;
            }

            $days = $config['days'] ?? [];
            if (is_array($days) && !empty($days) && !in_array($dayOfWeek, $days, true)) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function matchesDateConfig(array $config, string $dateStr): bool
    {
        $dates = $this->extractDateList($config);
        return in_array($dateStr, $dates, true);
    }

    private function matchesDayConfig(array $config, string $dayOfWeek): bool
    {
        $days = $config['days'] ?? [];
        if (!is_array($days) || empty($days)) {
            return false;
        }

        return in_array($dayOfWeek, $days, true);
    }

    private function extractDateList(array $config): array
    {
        $rawDates = $config['dates'] ?? [];

        if (is_array($rawDates)) {
            $dates = $rawDates;
        } else {
            $dates = preg_split('/[,\n]+/', (string) $rawDates) ?: [];
        }

        $normalized = [];
        foreach ($dates as $date) {
            $date = trim((string) $date);
            if ($date === '') {
                continue;
            }

            try {
                $normalized[] = Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Ignore invalid date strings and keep processing the rest.
            }
        }

        return array_values(array_unique($normalized));
    }

}
