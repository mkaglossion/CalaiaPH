<?php

namespace Tests\Feature;

use App\Models\Staycation;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaycationRoomInventoryLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_more_than_four_rooms_when_inventory_is_available(): void
    {
        $this->seedInventory(6, 8);

        $rooms = array_fill(0, 5, [
            'adults' => 1,
            'children' => 0,
            'pets' => 0,
        ]);

        $this->postJson(route('booking.saveSession'), [
            'checkin' => '2026-09-10',
            'checkout' => '2026-09-12',
            'rooms_count' => 5,
            'adults_total' => 5,
            'children_total' => 0,
            'pets_total' => 0,
            'rooms' => $rooms,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertSessionHas('booking.rooms_count', 5);
    }

    public function test_room_request_cannot_exceed_the_lowest_inventory_across_selected_nights(): void
    {
        $this->seedInventory(6, 8);

        $rooms = array_fill(0, 7, [
            'adults' => 1,
            'children' => 0,
            'pets' => 0,
        ]);

        $this->postJson(route('booking.saveSession'), [
            'checkin' => '2026-09-10',
            'checkout' => '2026-09-12',
            'rooms_count' => 7,
            'adults_total' => 7,
            'children_total' => 0,
            'pets_total' => 0,
            'rooms' => $rooms,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rooms_count')
            ->assertJsonPath('errors.rooms_count.0', 'Only 6 rooms are available across all selected nights.');
    }

    public function test_availability_page_has_unlimited_room_slot_controls_without_the_old_counter(): void
    {
        $this->seedInventory(8, 8);

        $this->withSession([
            'booking' => [
                'checkin' => '2026-09-10',
                'checkout' => '2026-09-12',
                'rooms_count' => 1,
                'adults_total' => 1,
                'children_total' => 0,
                'rooms' => [['adults' => 1, 'children' => 0, 'pets' => 0]],
            ],
        ])
            ->get(route('booking.staycationAvailability'))
            ->assertOk()
            ->assertSee('data-room-slot-controls', false)
            ->assertSee('data-staycation-price-display', false)
            ->assertSee('data-price-kind="single"', false)
            ->assertSeeText('Starting at ₱5,000')
            ->assertSee('ml-auto flex w-full sm:w-auto items-center justify-end', false)
            ->assertSee('maxSlots: Math.max(Number(targetRooms) || 1, 1)', false)
            ->assertSee('this.maxSlots++', false)
            ->assertDontSee('>Slots:</span>', false)
            ->assertDontSee('Target:', false)
            ->assertDontSee('x-show="maxSlots < 4"', false)
            ->assertDontSee('You cannot add more than 4 rooms.');
    }

    public function test_price_range_does_not_include_the_starting_at_prefix(): void
    {
        $this->seedInventory(8, 8);

        $pricingService = \Mockery::mock(PricingService::class);
        $pricingService
            ->shouldReceive('getStaycationRate')
            ->andReturnUsing(static function (Staycation $staycation, Carbon $date, string $seasonKey): float {
                return $date->format('Y-m-d') === '2026-09-10' ? 5000 : 6000;
            });
        $this->app->instance(PricingService::class, $pricingService);

        $this->withSession([
            'booking' => [
                'checkin' => '2026-09-10',
                'checkout' => '2026-09-12',
                'rooms_count' => 1,
                'adults_total' => 1,
                'children_total' => 0,
                'rooms' => [['adults' => 1, 'children' => 0, 'pets' => 0]],
            ],
        ])
            ->get(route('booking.staycationAvailability'))
            ->assertOk()
            ->assertSee('data-price-kind="range"', false)
            ->assertSeeText('₱5,000 - ₱6,000')
            ->assertDontSeeText('Starting at ₱5,000');
    }

    public function test_calendar_price_preview_accepts_more_than_four_rooms(): void
    {
        $this->seedInventory(8, 8);

        $this->postJson(route('api.pricing.staycationCalendar'), [
            'checkin' => '2026-09-10',
            'checkout' => '2026-09-12',
            'rooms' => 7,
            'adults' => 7,
            'children' => 0,
            'pets' => 0,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function seedInventory(int $firstNightQuantity, int $secondNightQuantity): void
    {
        $staycation = Staycation::create([
            'name' => 'Flexible Room Inventory',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => max($firstNightQuantity, $secondNightQuantity),
            'capacity' => 2,
            'max_guest_capacity' => 2,
            'included_guest_capacity' => 2,
            'adults' => 2,
            'children' => 0,
            'price' => 5000,
        ]);

        foreach ([
            '2026-09-10' => $firstNightQuantity,
            '2026-09-11' => $secondNightQuantity,
        ] as $date => $availableQuantity) {
            DB::table('staycation_inventories')->insert([
                'staycation_id' => $staycation->id,
                'date' => $date,
                'total_quantity' => $availableQuantity,
                'booked_quantity' => 0,
                'available_quantity' => $availableQuantity,
                'is_blocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
