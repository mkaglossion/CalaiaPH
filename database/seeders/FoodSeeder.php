<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Food;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foods = [
            // Breakfast options
            [
                'name' => 'Continental Breakfast',
                'category' => 'messhall',
                'price' => 250.00,
                'description' => 'Freshly baked croissants, pastries, fruits, yogurt, coffee, and tea.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Filipino Breakfast',
                'category' => 'messhall',
                'price' => 300.00,
                'description' => 'Traditional Filipino breakfast with tapsilog, eggs, and fresh fruits.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Healthy Breakfast Bowl',
                'category' => 'messhall',
                'price' => 280.00,
                'description' => 'Quinoa bowl with avocado, eggs, vegetables, and herbs.',
                'is_active' => true,
                'is_special_option' => false,
            ],

            // Lunch options
            [
                'name' => 'Grilled Chicken Salad',
                'category' => 'messhall',
                'price' => 350.00,
                'description' => 'Grilled chicken breast with mixed greens, cherry tomatoes, cucumber, and balsamic vinaigrette.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Seafood Paella',
                'category' => 'messhall',
                'price' => 450.00,
                'description' => 'Traditional Spanish rice dish with fresh seafood, saffron, and vegetables.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Vegetarian Pasta',
                'category' => 'messhall',
                'price' => 320.00,
                'description' => 'Pasta with seasonal vegetables, olive oil, garlic, and parmesan cheese.',
                'is_active' => true,
                'is_special_option' => false,
            ],

            // Dinner options
            [
                'name' => 'Grilled Salmon',
                'category' => 'messhall',
                'price' => 500.00,
                'description' => 'Fresh salmon fillet grilled to perfection, served with roasted vegetables and rice.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Beef Tenderloin',
                'category' => 'messhall',
                'price' => 600.00,
                'description' => 'Premium beef tenderloin with herb butter, mashed potatoes, and seasonal vegetables.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Vegetable Stir Fry',
                'category' => 'messhall',
                'price' => 350.00,
                'description' => 'Mixed vegetables stir-fried with tofu, served with steamed rice.',
                'is_active' => true,
                'is_special_option' => false,
            ],

            // Package options
            [
                'name' => 'Full Day Package',
                'category' => 'messhall',
                'price' => 1200.00,
                'description' => 'Includes breakfast, lunch, and dinner with beverages.',
                'is_active' => true,
                'is_special_option' => false,
            ],
            [
                'name' => 'Half Day Package',
                'category' => 'messhall',
                'price' => 600.00,
                'description' => 'Includes lunch and dinner with beverages.',
                'is_active' => true,
                'is_special_option' => false,
            ],

            // Special options
            [
                'name' => 'Dietary Restrictions Meal',
                'category' => 'messhall',
                'price' => 400.00,
                'description' => 'Customized meal for specific dietary needs (gluten-free, vegan, etc.). Please specify requirements.',
                'is_active' => true,
                'is_special_option' => true,
            ],
            [
                'name' => 'Kids Meal',
                'category' => 'messhall',
                'price' => 200.00,
                'description' => 'Fun and nutritious meal options for children.',
                'is_active' => true,
                'is_special_option' => true,
            ],
        ];

        foreach ($foods as $food) {
            Food::firstOrCreate(['name' => $food['name']], $food);
        }
    }
}
