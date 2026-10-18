<?php

namespace Tests\Unit\Services;

use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\DurationSet;
use App\Models\Holiday;
use App\Models\PeakPeriod;
use App\Models\PeakType;
use App\Models\PricingConfig;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = app(PricingService::class);
    }

    public function test_calculates_nightly_rate_from_the_price_matrix(): void
    {
        $this->seedPricingMatrix();

        $result = $this->pricingService->calculateNightlyRate('cabin', '2030-01-07');

        $this->assertTrue($result['success']);
        $this->assertSame(1000.0, (float) $result['rate']);
        $this->assertSame('Non-Peak', $result['season_type']);
        $this->assertSame('Weekday', $result['day_type']);
    }

    public function test_calculates_multiple_nights_and_returns_breakdown(): void
    {
        $this->seedPricingMatrix();

        $result = $this->pricingService->calculateBookingPrice('cabin', '2030-01-07', '2030-01-09');

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['nights']);
        $this->assertSame(2000.0, (float) $result['total_price']);
        $this->assertCount(2, $result['breakdown']);
    }

    public function test_holiday_takes_precedence_over_weekend(): void
    {
        $this->seedPricingMatrix();
        Holiday::create(['date' => '2030-01-05', 'name' => 'Test Holiday']);

        $result = $this->pricingService->calculateNightlyRate('cabin', '2030-01-05');

        $this->assertSame('Holiday', $result['day_type']);
        $this->assertSame(3000.0, (float) $result['rate']);
    }

    public function test_higher_priority_peak_period_takes_precedence(): void
    {
        $this->seedPricingMatrix();
        $peak = PeakType::where('name', 'Peak')->firstOrFail();
        $superPeak = PeakType::where('name', 'Super-Peak')->firstOrFail();
        PeakPeriod::create(['peak_type_id' => $peak->id, 'start_date' => '2030-01-08', 'end_date' => '2030-01-08']);
        PeakPeriod::create(['peak_type_id' => $superPeak->id, 'start_date' => '2030-01-08', 'end_date' => '2030-01-08']);

        $result = $this->pricingService->calculateNightlyRate('cabin', '2030-01-08');

        $this->assertSame('Super-Peak', $result['season_type']);
        $this->assertSame(4000.0, (float) $result['rate']);
    }

    public function test_returns_failure_for_missing_configuration_and_invalid_dates(): void
    {
        $missing = $this->pricingService->calculateBookingPrice('villa', '2030-01-07', '2030-01-08');
        $invalid = $this->pricingService->calculateBookingPrice('cabin', '2030-01-08', '2030-01-08');

        $this->assertFalse($missing['success']);
        $this->assertFalse($invalid['success']);
    }

    public function test_reuses_pricing_lookups_for_repeated_nightly_rate_calculations(): void
    {
        $this->seedPricingMatrix();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        $first = $this->pricingService->calculateNightlyRate('cabin', '2030-01-07');
        $second = $this->pricingService->calculateNightlyRate('cabin', '2030-01-07');

        $this->assertSame($first, $second);
        foreach ([
            'pricing_configs' => 1,
            'holidays' => 1,
            'day_types' => 2,
            'day_type_days' => 1,
            'peak_types' => 3,
            'peak_periods' => 1,
        ] as $table => $expectedCount) {
            $tableQueries = array_filter($queries, fn (string $sql): bool => str_contains($sql, $table));
            $this->assertCount($expectedCount, $tableQueries, "Unexpected number of {$table} queries.");
        }
    }

    private function seedPricingMatrix(): void
    {
        $durationSet = DurationSet::create(['name' => 'Nightly', 'duration' => 1]);
        $weekday = DayType::create(['name' => 'Weekday']);
        $weekend = DayType::create(['name' => 'Weekend']);
        $holiday = DayType::create(['name' => 'Holiday']);
        foreach ([0, 6] as $weekdayNumber) {
            DayTypeDay::create(['day_type_id' => $weekend->id, 'weekday' => $weekdayNumber]);
        }

        $nonPeak = PeakType::create(['name' => 'Non-Peak', 'priority' => 1]);
        $peak = PeakType::create(['name' => 'Peak', 'priority' => 2]);
        $superPeak = PeakType::create(['name' => 'Super-Peak', 'priority' => 3]);
        PeakPeriod::create(['peak_type_id' => $nonPeak->id, 'start_date' => '2029-01-01', 'end_date' => '2031-12-31']);

        foreach ([[$nonPeak, 1000], [$peak, 2000], [$superPeak, 4000]] as [$peakType, $price]) {
            foreach ([[$weekday, $price], [$weekend, $price + 500], [$holiday, $price + 2000]] as [$dayType, $dayPrice]) {
                PricingConfig::create([
                    'unit_type' => 'cabin',
                    'peak_type_id' => $peakType->id,
                    'day_type_id' => $dayType->id,
                    'duration_set_id' => $durationSet->id,
                    'price' => $dayPrice,
                ]);
            }
        }
    }
}
