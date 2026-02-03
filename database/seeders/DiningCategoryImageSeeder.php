<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DiningCategoryImage;

class DiningCategoryImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default dining category images
        // Note: These are placeholder Cloudinary URLs. Replace with actual uploaded images from the admin panel.
        
        DiningCategoryImage::firstOrCreate(
            ['category' => 'messhall'],
            [
                'image_path' => 'https://via.placeholder.com/1200x400?text=Messhall',
                'public_id' => null,
            ]
        );

        DiningCategoryImage::firstOrCreate(
            ['category' => 'cook_your_own'],
            [
                'image_path' => 'https://via.placeholder.com/1200x400?text=Cook+Your+Own',
                'public_id' => null,
            ]
        );
    }
}
