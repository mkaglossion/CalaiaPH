<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Food;

class CookYourOwnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if Cook Your Own already exists
        $exists = Food::where('name', 'Cook Your Own')->exists();
        
        if (!$exists) {
            Food::create([
                'name' => 'Cook Your Own',
                'category' => 'package',
                'price' => 0,
                'description' => 'We provide a fully equipped kitchen. Bring your own ingredients or use our kitchen facilities to prepare your own meals.',
                'is_active' => true,
                'is_special_option' => true,
            ]);
        }
    }
}
