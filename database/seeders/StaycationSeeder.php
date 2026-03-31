<?php

namespace Database\Seeders;

use App\Models\Staycation;
use Illuminate\Database\Seeder;

class StaycationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staycations = [
            [
                'name' => 'Standard Glamping Tent',
                'days' => 2,
                'type' => 'glamping',
                'base_quantity' => 5,
                'capacity' => 2,
                'adults' => 2,
                'children' => 1,
                'price' => 5440,
            ],
            [
                'name' => 'Premium Dome Tent',
                'days' => 2,
                'type' => 'glamping',
                'base_quantity' => 8,
                'capacity' => 4,
                'adults' => 4,
                'children' => 2,
                'price' => 6800,
            ],
            [
                'name' => 'Deluxe Air-Conditioned Tent',
                'days' => 2,
                'type' => 'glamping',
                'base_quantity' => 3,
                'capacity' => 6,
                'adults' => 4,
                'children' => 2,
                'price' => 7800,
            ],
            [
                'name' => 'Basic Camping Tent',
                'days' => 2,
                'type' => 'camping',
                'base_quantity' => 10,
                'capacity' => 4,
                'adults' => 2,
                'children' => 2,
                'price' => 2400,
            ],
            [
                'name' => 'Family Camping Hut',
                'days' => 2,
                'type' => 'camping',
                'base_quantity' => 5,
                'capacity' => 6,
                'adults' => 4,
                'children' => 2,
                'price' => 3600,
            ],
        ];

        foreach ($staycations as $staycation) {
            Staycation::create($staycation);
        }
    }
}
