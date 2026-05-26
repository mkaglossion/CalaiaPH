<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $calabarzon = Region::where('psgc_code', '0400000000')->first();
        if (!$calabarzon) {
            return;
        }

        $rows = [
            ['psgc_code' => '0401000000', 'name' => 'Batangas'],
            ['psgc_code' => '0458000000', 'name' => 'Rizal'],
            ['psgc_code' => '0434000000', 'name' => 'Laguna'],
            ['psgc_code' => '0421000000', 'name' => 'Cavite'],
        ];

        foreach ($rows as $row) {
            Province::updateOrCreate(
                ['psgc_code' => $row['psgc_code']],
                [
                    'region_id' => $calabarzon->id,
                    'name' => $row['name'],
                ]
            );
        }
    }
}
