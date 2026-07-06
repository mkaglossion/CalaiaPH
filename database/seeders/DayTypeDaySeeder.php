<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DayType;
use App\Models\DayTypeDay;

class DayTypeDaySeeder extends Seeder
{
    public function run(): void
    {
        // Ensure DayTypes exist
        $weekday = DayType::where('name', 'Weekday')->first();
        $weekend = DayType::where('name', 'Weekend')->first();

        if (!$weekday || !$weekend) {
            return; // Skip if DayTypes not seeded yet
        }

        // Weekend: Fri (5), Sat (6)
        $weekendDays = [5, 6];

        // Weekday: Sun (0), Mon (1), Tue (2), Wed (3), Thu (4)
        $weekdayDays = [0, 1, 2, 3, 4];

        foreach ($weekendDays as $day) {
            DayTypeDay::updateOrCreate([
                'day_type_id' => $weekend->id,
                'weekday' => $day,
            ], []);
        }

        foreach ($weekdayDays as $day) {
            DayTypeDay::updateOrCreate([
                'day_type_id' => $weekday->id,
                'weekday' => $day,
            ], []);
        }
    }
}
