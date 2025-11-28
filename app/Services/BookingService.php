<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmation;
use App\Mail\SpecialEventBookingConfirmation;

class BookingService
{
    /**
     * Validate and store selected activities in session
     */
    public function saveActivities(array $activities, string $sessionKey, ?array $customRules = null): void
    {
        $rules = $customRules ?? ValidationService::activitiesRules();

        // Note: Validation should be done in controller, this just saves
        session([$sessionKey => $activities]);
    }

    /**
     * Send booking confirmation email
     */
    public function sendConfirmationEmail(array $bookingData, array $activities, ?string $emailClass = null): void
    {
        $emailClass = $emailClass ?: BookingConfirmation::class;

        try {
            $email = $bookingData['email'] ?? 'guest@example.com';
            Log::info('Sending confirmation email to: ' . $email);
            Mail::to($email)->send(new $emailClass($bookingData, $activities));
            Log::info('Email sent successfully');
        } catch (\Exception $e) {
            Log::error('Email failed: ' . $e->getMessage());
        }
    }

    /**
     * Calculate total price including activities
     */
    public function calculateTotal(array $bookingData, array $activities): float
    {
        $basePrice = $bookingData['price'] ?? 0;
        $activitiesPrice = collect($activities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });
        return $basePrice + $activitiesPrice;
    }

    /**
     * Store booking confirmation data in session
     */
    public function saveBookingForConfirmation(string $prefix, array $bookingData, array $activities, float $total, array $customerInfo): void
    {
        session([
            $prefix . '_booking_data' => $bookingData,
            $prefix . '_selected_activities' => $activities,
            $prefix . '_grand_total' => $total,
            $prefix . '_customer_info' => $customerInfo,
        ]);
    }

    /**
     * Get activities data (centralized)
     */
    public function getActivitiesData(): array
    {
        // Centralized activities data from the activities page
        $activitiesByCategory = [
            'adventure' => [
                [
                    'id' => 1,
                    'name' => 'Go-Kart (15yo above)',
                    'description' => '4 rounds / 10 mins (approx)',
                    'duration' => '10 minutes',
                    'price' => 500,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/go-cart2.jpg'),
                ],
                [
                    'id' => 2,
                    'name' => 'ATV (15yo above)',
                    'description' => '20 minutes',
                    'duration' => '20 minutes',
                    'price' => 800,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/atv.jpg'),
                ],
                [
                    'id' => 3,
                    'name' => 'Wet Inflatable Slide',
                    'description' => '₱350 per child per hour, Minimum of 3 children or ₱1,050',
                    'duration' => '1 hour',
                    'price' => 350,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slide-n-go2.jpg'),
                ],
                [
                    'id' => 4,
                    'name' => 'Mini Kalesa',
                    'description' => '30 minutes (200 lbs max)',
                    'duration' => '30 minutes',
                    'price' => 600,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/minikalesa.jpg'),
                ],
                [
                    'id' => 5,
                    'name' => 'Horseback Riding',
                    'description' => '30 minutes',
                    'duration' => '30 minutes',
                    'price' => 600,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/our-farm2.jpg'),
                ],
            ],

            'sports' => [
                [
                    'id' => 6,
                    'name' => 'Mountain Bike',
                    'description' => 'Duration: 1 hour',
                    'duration' => '1 hour',
                    'price' => 300,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/bikes.jpg'),
                ],
                [
                    'id' => 7,
                    'name' => 'Archery Tag | Target',
                    'description' => 'Duration: 45 minutes',
                    'duration' => '45 minutes',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/archery.jpg'),
                ],
                [
                    'id' => 8,
                    'name' => 'Disc Golf',
                    'description' => 'Rate: ₱250 per game',
                    'duration' => '1 hour',
                    'price' => 250,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 9,
                    'name' => 'Kiteflying',
                    'description' => 'Duration: 1 hour',
                    'duration' => '1 hour',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 10,
                    'name' => 'Board Games / Cards',
                    'description' => 'Casual indoor board and card games.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 11,
                    'name' => 'Sandy Pickle',
                    'description' => 'Sand pickleball-style game.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
                [
                    'id' => 12,
                    'name' => 'Frisbee',
                    'description' => 'Casual throwing game in the field.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
                [
                    'id' => 13,
                    'name' => 'Field Volleyball',
                    'description' => 'Outdoor volleyball on the grass field.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
            ],

            'rrcamp' => [
                [
                    'id' => 14,
                    'name' => 'Kawa Bath',
                    'description' => '1 hour (1–4 persons), ₱1,000 hot / ₱2,000 cold',
                    'duration' => '1 hour',
                    'price' => 1000,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Kawa bath.jpg'),
                ],
                [
                    'id' => 15,
                    'name' => 'Bonfire | S\'mores',
                    'description' => '₱350 bonfire, ₱450 s\'mores (25 pcs)',
                    'duration' => '2 hours',
                    'price' => 350,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/bonfire.jpg'),
                ],
                [
                    'id' => 16,
                    'name' => 'Hiking',
                    'description' => 'Enjoy scenic nature trails around the property.',
                    'duration' => '2 hours',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 17,
                    'name' => 'Fishing',
                    'description' => '1 hour with cup of bait, ₱250 per rod',
                    'duration' => '1 hour',
                    'price' => 250,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
                ],
                [
                    'id' => 18,
                    'name' => 'Outdoor Movie',
                    'description' => 'Strictly 6–9 PM only',
                    'duration' => '3 hours',
                    'price' => 1500,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
                ],
            ],

            'farm' => [
                [
                    'id' => 19,
                    'name' => 'Tree Planting',
                    'description' => '₱200 per tree, Includes name label on the tree',
                    'duration' => '1 hour',
                    'price' => 200,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-1164.jpg'),
                ],
                [
                    'id' => 20,
                    'name' => 'Farm Tour / Animal Feeding',
                    'description' => 'Guided tour with friendly farm animals',
                    'duration' => '1 hour',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-880.jpg'),
                ],
                [
                    'id' => 21,
                    'name' => 'Veggie Picking',
                    'description' => 'Prices based on market price that day',
                    'duration' => '30 minutes',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-880.jpg'),
                ],
                [
                    'id' => 22,
                    'name' => 'Animal Encounters',
                    'description' => 'Close-up interactions with farm animals',
                    'duration' => '30 minutes',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/our-farm2.jpg'),
                ],
            ],
        ];

        // Flatten all activities into a single array
        $activities = [];
        foreach ($activitiesByCategory as $category => $categoryActivities) {
            $activities = array_merge($activities, $categoryActivities);
        }

        return $activities;
    }
}