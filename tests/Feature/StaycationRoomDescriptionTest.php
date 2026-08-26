<?php

namespace Tests\Feature;

use App\Models\Staycation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaycationRoomDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_the_description_rendered_on_the_availability_card(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $room = Staycation::create([
            'name' => 'Dynamic Description Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 2,
            'capacity' => 2,
            'max_guest_capacity' => 2,
            'included_guest_capacity' => 2,
            'adults' => 2,
            'children' => 0,
            'price' => 5000,
        ]);
        $description = 'A private nature escape managed from the room editor.';

        $this->actingAs($admin)
            ->get(route('admin.stay.staycations'))
            ->assertOk()
            ->assertSee('Room Description')
            ->assertSee('x-model="form.room_description"', false);

        $this->actingAs($admin)
            ->putJson(route('admin.staycation-list.update', $room), [
                'name' => $room->name,
                'days' => 1,
                'adults' => 2,
                'children' => 0,
                'included_guest_capacity' => 2,
                'max_guest_capacity' => 2,
                'description' => $description,
            ])
            ->assertOk()
            ->assertJsonPath('staycation.room_description', $description);

        $this->assertDatabaseHas('staycation_texts', [
            'staycation_id' => $room->id,
            'section' => 'description',
            'title' => 'Room Description',
            'content' => $description,
        ]);

        foreach (['2026-09-10', '2026-09-11'] as $date) {
            DB::table('staycation_inventories')->insert([
                'staycation_id' => $room->id,
                'date' => $date,
                'total_quantity' => 2,
                'booked_quantity' => 0,
                'available_quantity' => 2,
                'is_blocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->withSession([
            'booking' => [
                'checkin' => '2026-09-10',
                'checkout' => '2026-09-12',
                'rooms_count' => 1,
                'adults_total' => 2,
                'children_total' => 0,
            ],
        ])
            ->get(route('booking.staycationAvailability'))
            ->assertOk()
            ->assertSee($description)
            ->assertDontSee('Experience the beauty of nature with our premium accommodation options.');
    }

    public function test_room_description_stays_blank_when_not_configured(): void
    {
        $room = Staycation::create([
            'name' => 'Room Without Description',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 1,
            'capacity' => 2,
            'adults' => 2,
            'children' => 0,
            'price' => 5000,
        ]);

        $this->assertSame('', $room->resolveRoomDescription());
    }
}
