<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingConfig;
use App\Models\PeakType;
use App\Models\DayType;
use App\Models\DurationSet;

class PricingConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Get IDs
        $nonPeak = PeakType::where('name', 'Non-Peak')->first();
        $peak = PeakType::where('name', 'Peak')->first();
        $superPeak = PeakType::where('name', 'Super-Peak')->first();

        $holiday = DayType::where('name', 'Holiday')->first();
        $weekend = DayType::where('name', 'Weekend')->first();
        $weekday = DayType::where('name', 'Weekday')->first();

        $durationSet = DurationSet::where('name', 'Nightly Rate')->first();

        if (!$nonPeak || !$peak || !$superPeak || !$holiday || !$weekend || !$weekday || !$durationSet) {
            return; // Skip if types not seeded yet
        }

        // Clear existing pricing configs
        PricingConfig::truncate();

        $pricingData = [
            // Premium Glamping Tent
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Holiday',
                'price' => 7200,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Weekend',
                'price' => 6400,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Weekday',
                'price' => 5120,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Holiday',
                'price' => 8000,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Weekend',
                'price' => 7200,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Weekday',
                'price' => 5760,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Holiday',
                'price' => 8200,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Weekend',
                'price' => 7600,
            ],
            [
                'unit_type' => 'Premium Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Weekday',
                'price' => 7600,
            ],

            // Standard Glamping Tent
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Holiday',
                'price' => 6800,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Weekend',
                'price' => 6000,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Non-Peak',
                'day_type' => 'Weekday',
                'price' => 4800,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Holiday',
                'price' => 7600,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Weekend',
                'price' => 6800,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Peak',
                'day_type' => 'Weekday',
                'price' => 5440,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Holiday',
                'price' => 7800,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Weekend',
                'price' => 7200,
            ],
            [
                'unit_type' => 'Standard Glamping Tent',
                'season' => 'Super-Peak',
                'day_type' => 'Weekday',
                'price' => 7200,
            ],
        ];

        foreach ($pricingData as $data) {
            $peakTypeId = match($data['season']) {
                'Non-Peak' => $nonPeak->id,
                'Peak' => $peak->id,
                'Super-Peak' => $superPeak->id,
            };

            $dayTypeId = match($data['day_type']) {
                'Holiday' => $holiday->id,
                'Weekend' => $weekend->id,
                'Weekday' => $weekday->id,
            };

            PricingConfig::create([
                'unit_type' => $data['unit_type'],
                'peak_type_id' => $peakTypeId,
                'day_type_id' => $dayTypeId,
                'duration_set_id' => $durationSet->id,
                'price' => $data['price'],
            ]);
        }
    }
}