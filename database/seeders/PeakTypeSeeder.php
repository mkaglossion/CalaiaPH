<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeakType;

class PeakTypeSeeder extends Seeder
{
    public function run(): void
    {
        $peakTypes = [
            ['name' => 'Non-Peak', 'priority' => 1],
            ['name' => 'Peak', 'priority' => 2],
            ['name' => 'Super-Peak', 'priority' => 3],
        ];

        foreach ($peakTypes as $type) {
            PeakType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}