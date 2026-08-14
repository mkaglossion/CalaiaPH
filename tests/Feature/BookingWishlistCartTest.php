<?php

namespace Tests\Feature;

use App\Models\BookingWishlistItem;
use App\Models\Activity;
use App\Models\Food;
use App\Models\Staycation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingWishlistCartTest extends TestCase
{
    use RefreshDatabase;

    private array $context = [
        'checkin' => '2026-09-10',
        'checkout' => '2026-09-12',
    ];

    public function test_guest_cannot_use_booking_wishlist_endpoints(): void
    {
        $this->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSee('Wishlist')
            ->assertSee('Log in to use your Wishlist')
            ->assertSee('data-wishlist-login-panel', false)
            ->assertSee('@click="$store.modals.showLogin = true"', false)
            ->assertSee('href="' . route('booking.calendar') . '"', false);

        $this->postJson(route('booking.wishlist.toggle'), [
            'item_type' => 'food',
            'item_id' => 1,
        ])->assertUnauthorized();
    }

    public function test_booking_step_cards_render_save_controls_and_guest_login_modal_hook(): void
    {
        $room = $this->room();
        Food::create([
            'name' => 'Card Test Meal',
            'category' => 'messhall',
            'price' => 250,
            'is_active' => true,
            'stock_quantity' => 20,
            'is_inventory_managed' => true,
        ]);
        Activity::create([
            'name' => 'Card Test Activity',
            'category' => 'adventure',
            'price' => 500,
            'is_active' => true,
        ]);
        $booking = array_merge($this->context, [
            'booking_type' => 'staycation',
            'accommodations' => [[
                'room_id' => $room->id,
                'adults' => 2,
                'children' => 0,
                'total_price' => 5000,
            ]],
            'selected_foods' => [],
            'selected_activities' => [],
            'selected_activity_packages' => [],
            'final_totals' => ['accommodation_total' => 5000, 'food_total' => 0, 'activities_total' => 0, 'grand_total' => 5000],
        ]);

        $this->withSession(['booking' => $booking])
            ->get(route('booking.staycationAvailability'))
            ->assertOk()
            ->assertSee('data-booking-wishlist-button', false)
            ->assertSee('>Save<', false)
            ->assertSee('Open Booking Wishlist')
            ->assertSee(route('booking.wishlist.index'), false)
            ->assertSee('bi-cart3', false)
            ->assertDontSee('data-js-cart-button', false);

        $this->withSession(['booking' => $booking])
            ->get(route('booking.staycationFood'))
            ->assertOk()
            ->assertSee('data-wishlist-key="food:', false)
            ->assertSee('>Save<', false);

        $this->withSession(['booking' => $booking])
            ->get(route('booking.staycationActivities'))
            ->assertOk()
            ->assertSee('data-wishlist-key="activity:', false)
            ->assertSee('>Save<', false);
    }

    public function test_authenticated_user_can_toggle_a_configured_room_without_duplicate_records(): void
    {
        $user = User::factory()->create();
        $room = $this->room();
        $payload = [
            'item_type' => 'staycation',
            'item_id' => $room->id,
            'selection' => ['room_id' => $room->id, 'adults' => 2, 'children' => 1, 'price' => 1],
        ];

        $this->actingAs($user)->withSession(['booking' => $this->context])
            ->postJson(route('booking.wishlist.toggle'), $payload)
            ->assertOk()
            ->assertJson(['saved' => true, 'count' => 1]);

        $this->assertDatabaseCount('booking_wishlist_items', 1);
        $saved = BookingWishlistItem::firstOrFail();
        $this->assertSame(2, $saved->selection['adults']);
        $this->assertArrayNotHasKey('price', $saved->selection);

        $this->actingAs($user)->withSession(['booking' => $this->context])
            ->postJson(route('booking.wishlist.toggle'), $payload)
            ->assertOk()
            ->assertJson(['saved' => false, 'count' => 0]);

        $this->assertDatabaseCount('booking_wishlist_items', 0);
    }

    public function test_room_can_be_saved_for_later_using_booking_session_guest_defaults(): void
    {
        $user = User::factory()->create();
        $room = $this->room();

        $this->actingAs($user)->withSession(['booking' => array_merge($this->context, [
            'rooms_count' => 2,
            'adults_total' => 4,
            'children_total' => 2,
        ])])
            ->postJson(route('booking.wishlist.toggle'), [
                'item_type' => 'staycation',
                'item_id' => $room->id,
            ])
            ->assertOk()
            ->assertJson(['saved' => true, 'count' => 1]);

        $saved = BookingWishlistItem::firstOrFail();
        $this->assertSame($room->id, $saved->selection['room_id']);
        $this->assertSame(2, $saved->selection['adults']);
        $this->assertSame(1, $saved->selection['children']);
    }

    public function test_user_cannot_delete_another_users_wishlist_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $food = $this->food();
        $record = $this->wishlistItem($owner, 'food', $food->id, ['quantity' => 1]);

        $this->actingAs($other)
            ->delete(route('booking.wishlist.destroy', $record->id))
            ->assertNotFound();

        $this->assertDatabaseHas('booking_wishlist_items', ['id' => $record->id]);
    }

    public function test_wishlist_page_has_select_all_count_and_booking_cart_action(): void
    {
        $user = User::factory()->create();
        $food = $this->food();
        $this->wishlistItem($user, 'food', $food->id, ['quantity' => 1]);

        $this->actingAs($user)
            ->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSee('Items saved')
            ->assertSee('Select all')
            ->assertSee("selected.length + ' selected'", false)
            ->assertSee('Add selected to Booking Cart')
            ->assertSee('data-booking-wishlist-count', false);
    }

    public function test_wishlist_calculates_room_price_for_saved_dates_when_legacy_price_is_empty(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['price' => null, 'type' => 'glamping']);
        $this->wishlistItem($user, 'staycation', $room->id, [
            'room_id' => $room->id,
            'adults' => 2,
            'children' => 0,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSee('&#8369;11,000.00', false)
            ->assertSee('Estimated stay total');
    }

    public function test_selected_items_copy_to_booking_cart_and_leave_store_cart_untouched(): void
    {
        $user = User::factory()->create();
        $room = $this->room();
        $food = $this->food();
        $roomSaved = $this->wishlistItem($user, 'staycation', $room->id, ['room_id' => $room->id, 'adults' => 2]);
        $foodSaved = $this->wishlistItem($user, 'food', $food->id, ['quantity' => 2]);
        $storeCart = ['store-product-9' => ['quantity' => 3]];

        $response = $this->actingAs($user)->withSession(['cart' => $storeCart])
            ->post(route('booking.wishlist.add-to-cart'), [
                'items' => [$roomSaved->id, $foodSaved->id],
            ]);

        $response->assertRedirect(route('booking.cart.index'));
        $response->assertSessionHas('cart', $storeCart);
        $response->assertSessionHas('booking_cart.items.staycation:' . $room->id);
        $response->assertSessionHas('booking_cart.items.food:' . $food->id);
        $this->assertDatabaseCount('booking_wishlist_items', 2);

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('Open Booking Cart')
            ->assertSee('Room/tent quantity must be changed in the booking step.')
            ->assertSee('data-booking-cart-count', false)
            ->assertSee('>2</span>', false);
    }

    public function test_customer_can_update_activity_quantity_and_cart_totals(): void
    {
        $user = User::factory()->create();
        $activity = Activity::create([
            'name' => 'Test Go-Kart',
            'category' => 'adventure',
            'price' => 800,
            'is_active' => true,
        ]);
        $key = 'activity:' . $activity->id;
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                $key => [
                    'item_type' => 'activity',
                    'item_id' => $activity->id,
                    'booking_context' => $this->context,
                    'selection' => ['guests' => 1],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->patch(route('booking.cart.quantity.update', $key), ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.guests', 3);

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('Update quantity for Test Go-Kart')
            ->assertSee('>3</output>', false)
            ->assertSee('₱2,400.00')
            ->assertSee('>3</span>', false);
    }

    public function test_booking_cart_quantity_cannot_exceed_managed_stock(): void
    {
        $user = User::factory()->create();
        $food = $this->food();
        $food->update(['stock_quantity' => 2]);
        $key = 'food:' . $food->id;
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                $key => [
                    'item_type' => 'food',
                    'item_id' => $food->id,
                    'booking_context' => $this->context,
                    'selection' => ['quantity' => 1],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->patch(route('booking.cart.quantity.update', $key), ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas('error', 'Only 2 available for this item.')
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.quantity', 1);
    }

    public function test_cart_checkout_revalidates_inventory_and_does_not_create_a_booking(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['base_quantity' => 1]);
        DB::table('staycation_inventories')->insert([
            ['staycation_id' => $room->id, 'date' => '2026-09-10', 'available_quantity' => 0, 'total_quantity' => 1, 'booked_quantity' => 1, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
            ['staycation_id' => $room->id, 'date' => '2026-09-11', 'available_quantity' => 1, 'total_quantity' => 1, 'booked_quantity' => 0, 'is_blocked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                'staycation:' . $room->id => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => ['room_id' => $room->id, 'adults' => 2, 'quantity' => 1],
                ],
            ],
        ];

        $response = $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->post(route('booking.cart.checkout'));

        $response->assertRedirect(route('booking.cart.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cart_requires_an_accommodation_before_checkout(): void
    {
        $user = User::factory()->create();
        $food = $this->food();
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                'food:' . $food->id => [
                    'item_type' => 'food',
                    'item_id' => $food->id,
                    'booking_context' => $this->context,
                    'selection' => ['quantity' => 1],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->post(route('booking.cart.checkout'))
            ->assertRedirect(route('booking.cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_available_booking_cart_hydrates_existing_payment_session_without_finalizing_booking(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['base_quantity' => 2]);
        foreach (['2026-09-10', '2026-09-11'] as $date) {
            DB::table('staycation_inventories')->insert([
                'staycation_id' => $room->id,
                'date' => $date,
                'available_quantity' => 2,
                'total_quantity' => 2,
                'booked_quantity' => 0,
                'is_blocked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                'staycation:' . $room->id => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => ['room_id' => $room->id, 'adults' => 2, 'children' => 0, 'quantity' => 1],
                ],
            ],
        ];

        $response = $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->post(route('booking.cart.checkout'));

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHas('booking.booking_type', 'staycation');
        $response->assertSessionHas('booking.checkin', '2026-09-10');
        $response->assertSessionHas('booking.accommodations.0.room_id', $room->id);
        $this->assertDatabaseCount('bookings', 0);
    }

    private function wishlistItem(User $user, string $type, int $id, array $selection): BookingWishlistItem
    {
        return $user->bookingWishlistItems()->create([
            'item_type' => $type,
            'item_id' => $id,
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'selection' => $selection,
        ]);
    }

    private function room(array $overrides = []): Staycation
    {
        return Staycation::create(array_merge([
            'name' => 'Test Glamping Tent',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 5,
            'capacity' => 4,
            'adults' => 2,
            'children' => 2,
            'price' => 5000,
        ], $overrides));
    }

    private function food(): Food
    {
        return Food::create([
            'name' => 'Test Breakfast ' . fake()->uuid(),
            'category' => 'breakfast',
            'price' => 250,
            'is_active' => true,
            'stock_quantity' => 20,
            'is_inventory_managed' => true,
        ]);
    }
}
