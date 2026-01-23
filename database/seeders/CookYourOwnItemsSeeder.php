<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Food;

class CookYourOwnItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Beef
            ['name' => 'T-bone Steak', 'subcategory' => 'beef', 'price' => 450],
            ['name' => 'Rib Eye', 'subcategory' => 'beef', 'price' => 400],
            ['name' => 'Burger Patties', 'subcategory' => 'beef', 'price' => 150],
            ['name' => 'Beef Sausage', 'subcategory' => 'beef', 'price' => 120],
            ['name' => 'Bone Marrow', 'subcategory' => 'beef', 'price' => 200],

            // Pork
            ['name' => 'Kurobota Belly', 'subcategory' => 'pork', 'price' => 350],
            ['name' => 'K Shoulder', 'subcategory' => 'pork', 'price' => 250],
            ['name' => 'K Sausages', 'subcategory' => 'pork', 'price' => 120],
            ['name' => 'Sisig', 'subcategory' => 'pork', 'price' => 180],
            ['name' => 'Pork Barbecue', 'subcategory' => 'pork', 'price' => 160],
            ['name' => 'Pork Liempo', 'subcategory' => 'pork', 'price' => 220],

            // Chicken
            ['name' => 'Whole Chicken', 'subcategory' => 'chicken', 'price' => 250],
            ['name' => 'Chicken Wings', 'subcategory' => 'chicken', 'price' => 180],
            ['name' => 'Chicken Thigh', 'subcategory' => 'chicken', 'price' => 160],

            // Others
            ['name' => 'Farm Fresh Eggs', 'subcategory' => 'others', 'price' => 80],
            ['name' => 'Salt/Pepper/Cooking Oil (25 grams)', 'subcategory' => 'others', 'price' => 50],
            ['name' => 'Marinade (120 grams)', 'subcategory' => 'others', 'price' => 60],

            // Fruits and Vegetables
            ['name' => 'Corn', 'subcategory' => 'vegetables', 'price' => 40],
            ['name' => 'Taro', 'subcategory' => 'vegetables', 'price' => 30],
            ['name' => 'Okra', 'subcategory' => 'vegetables', 'price' => 50],
            ['name' => 'Eggplant', 'subcategory' => 'vegetables', 'price' => 40],
            ['name' => 'Sweet Potatoes', 'subcategory' => 'vegetables', 'price' => 35],
            ['name' => 'Watermelon', 'subcategory' => 'fruits', 'price' => 80],
            ['name' => 'Pineapple', 'subcategory' => 'fruits', 'price' => 60],
            ['name' => 'Mango', 'subcategory' => 'fruits', 'price' => 50],
            ['name' => 'Banana', 'subcategory' => 'fruits', 'price' => 30],
        ];

        foreach ($items as $item) {
            Food::firstOrCreate(
                ['name' => $item['name'], 'category' => 'cook_your_own'],
                [
                    'subcategory' => $item['subcategory'],
                    'price' => $item['price'],
                    'description' => '',
                    'is_active' => true,
                    'is_special_option' => false,
                    'stock_quantity' => 50, // Default stock
                    'is_inventory_managed' => true,
                ]
            );
        }
    }
}
