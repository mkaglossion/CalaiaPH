<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DayType;

class DayTypeSeeder extends Seeder
{
    public function run(): void
    {
        $dayTypes = [
            ['name' => 'Holiday'],
            ['name' => 'Weekend'],
            ['name' => 'Weekday'],
        ];

        foreach ($dayTypes as $type) {
            DayType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}