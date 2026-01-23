<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BookingSetting;

class BookingSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'mattress_price_per_night',
                'label' => 'Extra Mattress Price',
                'value' => 800,
                'description' => 'Price per extra mattress per night (₱)',
                'category' => 'pricing',
            ],
            [
                'key' => 'pet_price',
                'label' => 'Pet Fee',
                'value' => 2000,
                'description' => 'One-time pet fee per booking (₱)',
                'category' => 'pricing',
            ],
        ];

        foreach ($settings as $setting) {
            BookingSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

