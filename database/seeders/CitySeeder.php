<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $ncr = Region::where('psgc_code', '1300000000')->first();
        $calabarzon = Region::where('psgc_code', '0400000000')->first();
        if (!$ncr || !$calabarzon) {
            return;
        }

        $rizal = Province::where('psgc_code', '0458000000')->first();
        $laguna = Province::where('psgc_code', '0434000000')->first();
        $cavite = Province::where('psgc_code', '0421000000')->first();
        $batangas = Province::where('psgc_code', '0401000000')->first();

        $rows = [
            // NCR (no province_id)
            ['psgc_code' => '1380700000', 'name' => 'Marikina City', 'region_id' => $ncr->id, 'province_id' => null, 'type' => 'city'],
            ['psgc_code' => '1374000000', 'name' => 'Quezon City', 'region_id' => $ncr->id, 'province_id' => null, 'type' => 'city'],
            ['psgc_code' => '1376000000', 'name' => 'Pasig City', 'region_id' => $ncr->id, 'province_id' => null, 'type' => 'city'],
            ['psgc_code' => '1339000000', 'name' => 'City of Manila', 'region_id' => $ncr->id, 'province_id' => null, 'type' => 'city'],

            // CALABARZON
            ['psgc_code' => '0458030000', 'name' => 'Antipolo City', 'region_id' => $calabarzon->id, 'province_id' => optional($rizal)->id, 'type' => 'city'],
            ['psgc_code' => '0458040000', 'name' => 'Cainta', 'region_id' => $calabarzon->id, 'province_id' => optional($rizal)->id, 'type' => 'municipality'],
            ['psgc_code' => '0458100000', 'name' => 'Taytay', 'region_id' => $calabarzon->id, 'province_id' => optional($rizal)->id, 'type' => 'municipality'],
            ['psgc_code' => '0434140000', 'name' => 'San Pedro City', 'region_id' => $calabarzon->id, 'province_id' => optional($laguna)->id, 'type' => 'city'],
            ['psgc_code' => '0434040000', 'name' => 'Binan City', 'region_id' => $calabarzon->id, 'province_id' => optional($laguna)->id, 'type' => 'city'],
            ['psgc_code' => '0434280000', 'name' => 'Santa Rosa City', 'region_id' => $calabarzon->id, 'province_id' => optional($laguna)->id, 'type' => 'city'],
            ['psgc_code' => '0421010000', 'name' => 'Bacoor City', 'region_id' => $calabarzon->id, 'province_id' => optional($cavite)->id, 'type' => 'city'],
            ['psgc_code' => '0421060000', 'name' => 'Dasmarinas City', 'region_id' => $calabarzon->id, 'province_id' => optional($cavite)->id, 'type' => 'city'],
            ['psgc_code' => '0421090000', 'name' => 'Imus City', 'region_id' => $calabarzon->id, 'province_id' => optional($cavite)->id, 'type' => 'city'],
            // Batangas (local service area)
            ['psgc_code' => '0401007000', 'name' => 'Calaca', 'region_id' => $calabarzon->id, 'province_id' => optional($batangas)->id, 'type' => 'city'],
        ];

        foreach ($rows as $row) {
            City::updateOrCreate(
                ['psgc_code' => $row['psgc_code']],
                [
                    'name' => $row['name'],
                    'region_id' => $row['region_id'],
                    'province_id' => $row['province_id'],
                    'type' => $row['type'],
                ]
            );
        }
    }
}
