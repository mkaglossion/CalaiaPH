<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FarmExperience;

class FarmExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $experiences = [
            [
                'title' => 'Animal Feeding Experience',
                'desc' => 'Get up close with our farm animals. Learn about their care and dietary needs while you feed them.',
                'time' => 'Weekends 9AM–2PM',
                'capacity' => 'Groups up to 20 people',
                'icon' => 'people',
                'cta' => 'Reserve Your Spot',
                'badge' => null,
                'img' => null,
                'sort_order' => 0,
            ],
            [
                'title' => 'Harvest Days',
                'desc' => 'Participate in seasonal harvesting. Learn sustainable farming practices and take home fresh produce.',
                'time' => 'Morning hours seasonal',
                'capacity' => 'Take home harvest',
                'icon' => 'flower1',
                'cta' => 'Learn More',
                'badge' => null,
                'img' => null,
                'sort_order' => 1,
            ],
            [
                'title' => 'Farm Tours',
                'desc' => 'Explore our farm and learn about sustainable agriculture, animal welfare, and eco-friendly practices.',
                'time' => 'By appointment',
                'capacity' => 'Educational experience',
                'icon' => 'mortarboard',
                'cta' => 'Schedule Tour',
                'badge' => 'Guided Tour',
                'img' => null,
                'sort_order' => 2,
            ]
        ];

        foreach ($experiences as $experience) {
            FarmExperience::create($experience);
        }
    }
}
