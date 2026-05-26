<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\City;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $calaca = City::where('psgc_code', '0401007000')->first();
        if (!$calaca) {
            return;
        }

        // Source: PSA PSGC City of Calaca barangay list.
        $rows = [
            ['psgc_code' => '0401007001', 'name' => 'Bagong Tubig'],
            ['psgc_code' => '0401007002', 'name' => 'Baclas'],
            ['psgc_code' => '0401007003', 'name' => 'Balimbing'],
            ['psgc_code' => '0401007004', 'name' => 'Bambang'],
            ['psgc_code' => '0401007005', 'name' => 'Barangay 1'],
            ['psgc_code' => '0401007006', 'name' => 'Barangay 2'],
            ['psgc_code' => '0401007007', 'name' => 'Barangay 3'],
            ['psgc_code' => '0401007008', 'name' => 'Barangay 4'],
            ['psgc_code' => '0401007009', 'name' => 'Barangay 5'],
            ['psgc_code' => '0401007010', 'name' => 'Barangay 6'],
            ['psgc_code' => '0401007011', 'name' => 'Bisaya'],
            ['psgc_code' => '0401007012', 'name' => 'Cahil'],
            ['psgc_code' => '0401007013', 'name' => 'Caluangan'],
            ['psgc_code' => '0401007014', 'name' => 'Calantas'],
            ['psgc_code' => '0401007015', 'name' => 'Camastilisan'],
            ['psgc_code' => '0401007016', 'name' => 'Coral Ni Lopez'],
            ['psgc_code' => '0401007017', 'name' => 'Coral Ni Bacal'],
            ['psgc_code' => '0401007018', 'name' => 'Dacanlao'],
            ['psgc_code' => '0401007019', 'name' => 'Dila'],
            ['psgc_code' => '0401007020', 'name' => 'Loma'],
            ['psgc_code' => '0401007021', 'name' => 'Lumbang Calzada'],
            ['psgc_code' => '0401007022', 'name' => 'Lumbang Na Bata'],
            ['psgc_code' => '0401007023', 'name' => 'Lumbang Na Matanda'],
            ['psgc_code' => '0401007024', 'name' => 'Madalunot'],
            ['psgc_code' => '0401007025', 'name' => 'Makina'],
            ['psgc_code' => '0401007027', 'name' => 'Matipok'],
            ['psgc_code' => '0401007028', 'name' => 'Munting Coral'],
            ['psgc_code' => '0401007030', 'name' => 'Niyugan'],
            ['psgc_code' => '0401007031', 'name' => 'Pantay'],
            ['psgc_code' => '0401007032', 'name' => 'Puting Bato West'],
            ['psgc_code' => '0401007033', 'name' => 'Puting Kahoy'],
            ['psgc_code' => '0401007034', 'name' => 'Puting Bato East'],
            ['psgc_code' => '0401007035', 'name' => 'Quisumbing'],
            ['psgc_code' => '0401007036', 'name' => 'Salong'],
            ['psgc_code' => '0401007037', 'name' => 'Sinisian'],
            ['psgc_code' => '0401007039', 'name' => 'Taklang Anak'],
            ['psgc_code' => '0401007041', 'name' => 'Talisay'],
            ['psgc_code' => '0401007042', 'name' => 'Tamayo'],
            ['psgc_code' => '0401007043', 'name' => 'Timbain'],
        ];

        foreach ($rows as $row) {
            Barangay::updateOrCreate(
                ['psgc_code' => $row['psgc_code']],
                [
                    'city_id' => $calaca->id,
                    'name' => $row['name'],
                ]
            );
        }
    }
}
