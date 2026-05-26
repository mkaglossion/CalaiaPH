<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // PSGC-based starter set. Extend this using full PSGC dump as needed.
        $rows = [
            ['psgc_code' => '1300000000', 'name' => 'NCR'],
            ['psgc_code' => '0400000000', 'name' => 'CALABARZON'],
        ];

        foreach ($rows as $row) {
            Region::updateOrCreate(
                ['psgc_code' => $row['psgc_code']],
                ['name' => $row['name'], 'is_active' => true]
            );
        }
    }
}

