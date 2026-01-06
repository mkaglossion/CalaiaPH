<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            // Regular Holidays
            ['name' => 'New Year\'s Day', 'date' => '2025-01-01'],
            ['name' => 'Maundy Thursday', 'date' => '2025-04-02'],
            ['name' => 'Good Friday', 'date' => '2025-04-03'],
            ['name' => 'Araw ng Kagitingan', 'date' => '2025-04-09'],
            ['name' => 'Labor Day', 'date' => '2025-05-01'],
            ['name' => 'Independence Day', 'date' => '2025-06-12'],
            ['name' => 'National Heroes Day', 'date' => '2025-08-31'],
            ['name' => 'Bonifacio Day', 'date' => '2025-11-30'],
            ['name' => 'Christmas Day', 'date' => '2025-12-25'],
            ['name' => 'Rizal Day', 'date' => '2025-12-30'],

            // Special (Non-Working) Days
            ['name' => 'Ninoy Aquino Day', 'date' => '2025-08-21'],
            ['name' => 'All Saints\' Day', 'date' => '2025-11-01'],
            ['name' => 'Feast of the Immaculate Conception of Mary', 'date' => '2025-12-08'],
            ['name' => 'Last Day of the Year', 'date' => '2025-12-31'],

            // Special (Working) Day
            ['name' => 'EDSA People Power Revolution Anniversary', 'date' => '2025-02-25'],

            // Additional Special (Non-Working) Days
            ['name' => 'Chinese New Year', 'date' => '2025-02-17'],
            ['name' => 'Black Saturday', 'date' => '2025-04-04'],
            ['name' => 'All Souls\' Day', 'date' => '2025-11-02'],
            ['name' => 'Christmas Eve', 'date' => '2025-12-24'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                ['name' => $holiday['name']]
            );
        }
    }
}
