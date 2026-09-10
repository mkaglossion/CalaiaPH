<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\Food;
use App\Models\FoodPackage;
use App\Models\Staycation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaycationCatalogSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_availability_sorts_rooms_by_the_displayed_dynamic_rate(): void
    {
        $this->room('Alpha Budget Room', 1000, 1);
        $this->room('Zulu Premium Room', 5000, 2);

        $this->get(route('booking.staycationAvailability', [
            'checkin' => '2026-09-20',
            'checkout' => '2026-09-21',
            'sort_by' => 'price_desc',
        ]))
            ->assertOk()
            ->assertViewHas('staycations', fn ($rooms) => $rooms->pluck('name')->all() === [
                'Zulu Premium Room',
                'Alpha Budget Room',
            ]);
    }

    public function test_food_sorts_items_and_packages_by_their_displayed_prices(): void
    {
        $room = $this->room('Test Room', 1000, 1);
        $lowFood = $this->food('Alpha Budget Meal', 100);
        $highFood = $this->food('Zulu Premium Meal', 500);

        $lowPackage = $this->foodPackage('Alpha Budget Package', $lowFood);
        $highPackage = $this->foodPackage('Zulu Premium Package', $highFood, 3);

        $this->withSession($this->bookingSession($room))
            ->get(route('booking.staycationFood', ['sort_by' => 'price_desc']))
            ->assertOk()
            ->assertViewHas('foodMenus', fn ($foods) => $foods->pluck('name')->all() === [
                'Zulu Premium Meal',
                'Alpha Budget Meal',
            ])
            ->assertViewHas('foodPackages', fn ($packages) => $packages->pluck('id')->all() === [
                $highPackage->id,
                $lowPackage->id,
            ]);
    }

    public function test_activities_sort_items_and_packages_by_their_displayed_prices(): void
    {
        $room = $this->room('Test Room', 1000, 1);
        $lowActivity = $this->activity('Alpha Budget Activity', 100);
        $highActivity = $this->activity('Zulu Premium Activity', 500);

        $lowPackage = $this->activityPackage('Alpha Budget Package', $lowActivity);
        $highPackage = $this->activityPackage('Zulu Premium Package', $highActivity, 3);

        $this->withSession($this->bookingSession($room))
            ->get(route('booking.staycationActivities', ['sort_by' => 'price_desc']))
            ->assertOk()
            ->assertViewHas('activities', fn ($activities) => $activities->pluck('name')->all() === [
                'Zulu Premium Activity',
                'Alpha Budget Activity',
            ])
            ->assertViewHas('activityPackages', fn ($packages) => $packages->pluck('id')->all() === [
                $highPackage->id,
                $lowPackage->id,
            ]);
    }

    private function room(string $name, int $rate, int $sortOrder): Staycation
    {
        return Staycation::create([
            'name' => $name,
            'type' => 'glamping',
            'base_quantity' => 1,
            'capacity' => 2,
            'adults' => 2,
            'children' => 0,
            'price' => 0,
            'sort_order' => $sortOrder,
            'price_configs' => [[
                'name' => 'Standard rate',
                'type' => 'day',
                'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'rates' => [
                    'nonPeak' => $rate,
                    'peak' => $rate,
                    'superPeak' => $rate,
                ],
            ]],
        ]);
    }

    private function food(string $name, int $price): Food
    {
        return Food::create([
            'name' => $name,
            'category' => 'messhall',
            'meal_type' => 'all_day',
            'price' => $price,
            'is_active' => true,
        ]);
    }

    private function foodPackage(string $name, Food $food, int $quantity = 1): FoodPackage
    {
        $package = FoodPackage::create([
            'name' => $name,
            'price' => 0,
            'category' => 'overnight_stay_menu',
            'is_active' => true,
        ]);
        $package->foods()->attach($food->id, ['quantity' => $quantity]);

        return $package;
    }

    private function activity(string $name, int $price): Activity
    {
        return Activity::create([
            'name' => $name,
            'price' => $price,
            'category' => 'adventure',
            'is_active' => true,
        ]);
    }

    private function activityPackage(string $name, Activity $activity, int $quantity = 1): ActivityPackage
    {
        $package = ActivityPackage::create([
            'name' => $name,
            'price' => 0,
            'category' => 'special',
            'is_active' => true,
        ]);
        $package->activities()->attach($activity->id, ['quantity' => $quantity]);

        return $package;
    }

    private function bookingSession(Staycation $room): array
    {
        return [
            'booking' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-09-20',
                'checkout' => '2026-09-21',
                'accommodations' => [
                    $room->id => [
                        'staycation_id' => $room->id,
                        'room_name' => $room->name,
                        'quantity' => 1,
                        'total_price' => 1000,
                    ],
                ],
            ],
        ];
    }
}
