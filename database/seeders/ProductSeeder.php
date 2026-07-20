<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'title' => 'Grass-fed Beef Burger Patties (500g)',
                'description' => 'Juicy, all-natural beef patties made from our pasture-raised cattle. No preservatives.',
                'price' => 350.00,
                'image' => null,
                'category' => 'frozen',
            ],
            [
                'title' => 'Heritage Pork Sausages (500g)',
                'description' => 'Traditional recipe, no fillers, made from our free-range heritage pigs.',
                'price' => 280.00,
                'image' => 'Retreat-1164.jpg',
                'category' => 'frozen',
            ],
            [
                'title' => 'Pasture-raised Chicken Breast (1kg)',
                'description' => 'Tender, hormone-free chicken breast, perfect for healthy meals.',
                'price' => 420.00,
                'image' => 'Retreat-705.jpg',
                'category' => 'frozen',
            ],
            [
                'title' => 'Heirloom Tomatoes (500g)',
                'description' => 'Colorful, sweet, and juicy tomatoes grown without pesticides.',
                'price' => 120.00,
                'image' => 'organic.jpg',
                'category' => 'fresh',
            ],
            [
                'title' => 'Rainbow Chard (250g)',
                'description' => 'Vibrant, nutrient-rich chard, perfect for salads and sautés.',
                'price' => 90.00,
                'image' => null,
                'category' => 'fresh',
            ],
            [
                'title' => 'Zucchini (500g)',
                'description' => 'Fresh, tender zucchini, harvested daily from our fields.',
                'price' => 80.00,
                'image' => null,
                'category' => 'fresh',
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
