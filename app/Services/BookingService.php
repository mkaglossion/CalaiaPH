<?php

namespace App\Services;

class BookingService
{
    public function getActivitiesData()
    {
        // Return staycation activities data
        return [
            [
                'id' => 1,
                'name' => 'Go-Kart (15yo above)',
                'description' => '4 rounds / 10 mins (approx)',
                'duration' => '10 minutes',
                'price' => 500.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 2,
                'name' => 'ATV (15yo above)',
                'description' => '20 minutes',
                'duration' => '20 minutes',
                'price' => 800.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 3,
                'name' => 'Wet Inflatable Slide',
                'description' => '₱350 per child per hour, Minimum of 3 children or ₱1,050',
                'duration' => '1 hour',
                'price' => 350.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 4,
                'name' => 'Mini Kalesa',
                'description' => '30 minutes (200 lbs max)',
                'duration' => '30 minutes',
                'price' => 600.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 5,
                'name' => 'Horseback Riding',
                'description' => '30 minutes',
                'duration' => '30 minutes',
                'price' => 600.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 6,
                'name' => 'Mountain Bike',
                'description' => 'Duration: 1 hour',
                'duration' => '1 hour',
                'price' => 300.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 7,
                'name' => 'Archery Tag | Target',
                'description' => 'Duration: 45 minutes',
                'duration' => '45 minutes',
                'price' => 150.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 8,
                'name' => 'Disc Golf',
                'description' => 'Rate: ₱250 per game',
                'duration' => '1 hour',
                'price' => 250.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 9,
                'name' => 'Kiteflying',
                'description' => 'Duration: 1 hour',
                'duration' => '1 hour',
                'price' => 150.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 10,
                'name' => 'Board Games / Cards',
                'description' => 'Casual indoor board and card games.',
                'duration' => '1 hour',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 11,
                'name' => 'Sandy Pickle',
                'description' => 'Sand pickleball-style game.',
                'duration' => '1 hour',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 12,
                'name' => 'Frisbee',
                'description' => 'Casual throwing game in the field.',
                'duration' => '1 hour',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 13,
                'name' => 'Field Volleyball',
                'description' => 'Outdoor volleyball on the grass field.',
                'duration' => '1 hour',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 14,
                'name' => 'Kawa Bath',
                'description' => '1 hour (1–4 persons), ₱1,000 hot / ₱2,000 cold',
                'duration' => '1 hour',
                'price' => 1000.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 15,
                'name' => 'Bonfire | S\'mores',
                'description' => '₱350 bonfire, ₱450 s\'mores (25 pcs)',
                'duration' => '2 hours',
                'price' => 350.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 16,
                'name' => 'Hiking',
                'description' => 'Enjoy scenic nature trails around the property.',
                'duration' => '2 hours',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 17,
                'name' => 'Fishing',
                'description' => '1 hour with cup of bait, ₱250 per rod',
                'duration' => '1 hour',
                'price' => 250.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 18,
                'name' => 'Outdoor Movie',
                'description' => 'Strictly 6–9 PM only',
                'duration' => '3 hours',
                'price' => 1500.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 19,
                'name' => 'Tree Planting',
                'description' => '₱200 per tree, Includes name label on the tree',
                'duration' => '1 hour',
                'price' => 200.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 20,
                'name' => 'Farm Tour / Animal Feeding',
                'description' => 'Guided tour with friendly farm animals',
                'duration' => '1 hour',
                'price' => 150.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 21,
                'name' => 'Veggie Picking',
                'description' => 'Prices based on market price that day',
                'duration' => '30 minutes',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ],
            [
                'id' => 22,
                'name' => 'Animal Encounters',
                'description' => 'Close-up interactions with farm animals',
                'duration' => '30 minutes',
                'price' => 0.00,
                'availability' => 'Available',
                'type' => 'predefined'
            ]
        ];
    }

    public function saveActivities($activities, $saveKey)
    {
        // Save activities to session
        session([$saveKey => $activities]);
    }
}