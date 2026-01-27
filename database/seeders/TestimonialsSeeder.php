<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'content' => 'Calaia was an unforgettable experience. The farm stay was peaceful, the activities were fun, and the food was amazing. Highly recommend!',
                'name' => 'Sarah Johnson',
                'position' => 'Family Traveler',
                'image_path' => '/assets/images/testimonials/avatar1.jpg',
                'order' => 1,
                'status' => 'approved',
                'rating' => 5,
            ],
            [
                'content' => 'The staff was incredibly welcoming, and the farm-to-table experience was outstanding. We felt completely at home.',
                'name' => 'Michael Chen',
                'position' => 'Adventure Seeker',
                'image_path' => '/assets/images/testimonials/avatar2.jpg',
                'order' => 2,
                'status' => 'approved',
                'rating' => 5,
            ],
            [
                'content' => 'Perfect getaway for our anniversary. The romantic setting and personalized service made it truly special.',
                'name' => 'Emma Rodriguez',
                'position' => 'Romantic Getaway',
                'image_path' => '/assets/images/testimonials/avatar3.jpg',
                'order' => 3,
                'status' => 'approved',
                'rating' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
