<?php

namespace Database\Seeders;

use App\Models\DiningReservation;
use App\Models\Food;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DiningReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing foods or create if needed
        $foods = Food::all();
        
        if ($foods->isEmpty()) {
            // Create foods with consistent data
            $foodData = [
                ['name' => 'Grilled Fish', 'description' => 'Fresh grilled fish with herbs', 'price' => 450.00],
                ['name' => 'Beef Steak', 'description' => 'Premium beef steak', 'price' => 650.00],
                ['name' => 'Seafood Pasta', 'description' => 'Pasta with mixed seafood', 'price' => 550.00],
                ['name' => 'Chicken Adobo', 'description' => 'Filipino chicken adobo', 'price' => 380.00],
                ['name' => 'Vegetable Plate', 'description' => 'Fresh seasonal vegetables', 'price' => 280.00],
                ['name' => 'Shrimp Lumpia', 'description' => 'Crispy shrimp spring rolls', 'price' => 320.00],
            ];
            foreach ($foodData as $food) {
                Food::create($food);
            }
            $foods = Food::all();
        }

        $now = Carbon::now();

        // Create specific food assignments with payment info
        $reservations = [
            // Past reservation (confirmed, paid)
            [
                'food_id' => $foods->where('name', 'Grilled Fish')->first()->id ?? $foods->first()->id,
                'meal_period' => 'lunch',
                'reservation_date' => $now->clone()->subDays(5)->format('Y-m-d'),
                'reservation_time' => '12:00:00',
                'adults' => 2,
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'phone' => '09171234567',
                'email' => 'maria.santos@example.com',
                'special_requests' => 'No spicy food please',
                'status' => 'confirmed',
            ],
            // Today's reservation (confirmed, paid)
            [
                'food_id' => $foods->where('name', 'Beef Steak')->first()->id ?? $foods->skip(1)->first()->id,
                'meal_period' => 'dinner',
                'reservation_date' => $now->format('Y-m-d'),
                'reservation_time' => '19:00:00',
                'adults' => 4,
                'first_name' => 'John',
                'last_name' => 'David',
                'phone' => '09179876543',
                'email' => 'john.david@example.com',
                'special_requests' => 'Birthday celebration - please have decorations',
                'status' => 'confirmed',
            ],
            // Tomorrow's reservation (pending, unpaid)
            [
                'food_id' => $foods->where('name', 'Seafood Pasta')->first()->id ?? $foods->skip(2)->first()->id,
                'meal_period' => 'breakfast',
                'reservation_date' => $now->clone()->addDays(1)->format('Y-m-d'),
                'reservation_time' => '08:30:00',
                'adults' => 3,
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'phone' => '09185551234',
                'email' => 'ana.cruz@example.com',
                'special_requests' => null,
                'status' => 'pending',
            ],
            // 3 days from now (confirmed, paid)
            [
                'food_id' => $foods->where('name', 'Chicken Adobo')->first()->id ?? $foods->skip(3)->first()->id,
                'meal_period' => 'dinner',
                'reservation_date' => $now->clone()->addDays(3)->format('Y-m-d'),
                'reservation_time' => '18:30:00',
                'adults' => 6,
                'first_name' => 'Robert',
                'last_name' => 'Gomez',
                'phone' => '09165552222',
                'email' => 'robert.gomez@example.com',
                'special_requests' => 'Window seat preferred, anniversary dinner',
                'status' => 'confirmed',
            ],
            // Week from now (pending, unpaid)
            [
                'food_id' => $foods->where('name', 'Vegetable Plate')->first()->id ?? $foods->skip(4)->first()->id,
                'meal_period' => 'lunch',
                'reservation_date' => $now->clone()->addDays(7)->format('Y-m-d'),
                'reservation_time' => '12:30:00',
                'adults' => 2,
                'first_name' => 'Jessica',
                'last_name' => 'Torres',
                'phone' => '09175553333',
                'email' => 'jessica.torres@example.com',
                'special_requests' => 'Vegetarian options needed',
                'status' => 'pending',
            ],
            // Two weeks from now (confirmed, paid)
            [
                'food_id' => $foods->where('name', 'Shrimp Lumpia')->first()->id ?? $foods->last()->id,
                'meal_period' => 'dinner',
                'reservation_date' => $now->clone()->addDays(14)->format('Y-m-d'),
                'reservation_time' => '19:30:00',
                'adults' => 5,
                'first_name' => 'Michael',
                'last_name' => 'Lee',
                'phone' => '09185554444',
                'email' => 'michael.lee@example.com',
                'special_requests' => 'Private dining room if available',
                'status' => 'confirmed',
            ],
            // 3 days from now (pending, unpaid - another one)
            [
                'food_id' => $foods->where('name', 'Beef Steak')->first()->id ?? $foods->skip(1)->first()->id,
                'meal_period' => 'breakfast',
                'reservation_date' => $now->clone()->addDays(3)->format('Y-m-d'),
                'reservation_time' => '09:00:00',
                'adults' => 1,
                'first_name' => 'Sofia',
                'last_name' => 'Garcia',
                'phone' => '09165555555',
                'email' => 'sofia.garcia@example.com',
                'special_requests' => null,
                'status' => 'pending',
            ],
            // 6 days from now (confirmed, paid)
            [
                'food_id' => $foods->where('name', 'Grilled Fish')->first()->id ?? $foods->first()->id,
                'meal_period' => 'lunch',
                'reservation_date' => $now->clone()->addDays(6)->format('Y-m-d'),
                'reservation_time' => '13:00:00',
                'adults' => 4,
                'first_name' => 'Carlos',
                'last_name' => 'Reyes',
                'phone' => '09175556666',
                'email' => 'carlos.reyes@example.com',
                'special_requests' => 'Family gathering - high chairs needed for 2 kids',
                'status' => 'confirmed',
            ],
        ];

        foreach ($reservations as $reservation) {
            DiningReservation::create($reservation);
        }
    }
}
