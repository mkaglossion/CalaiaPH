<?php

namespace Tests\Feature;

use App\Models\BookingWishlistItem;
use App\Models\Activity;
use App\Models\BookingSetting;
use App\Models\Food;
use App\Models\FoodPackage;
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
            ->assertSee('>Save for later<', false)
            ->assertDontSee('data-wishlist-spinner', false)
            ->assertDontSee("'Saving...'", false)
            ->assertSee('setSaved(key, optimisticSaved)', false)
            ->assertSee('setCount(optimisticCount)', false)
            ->assertSee("title: saved ? 'Added to Wishlist' : 'Removed from Wishlist'", false)
            ->assertSee('<i class="bi bi-heart text-xl"></i>', false)
            ->assertSee("pets: 'no'", false)
            ->assertSee('Open Booking Wishlist')
            ->assertSee(route('booking.wishlist.index'), false)
            ->assertSee('bi-cart3', false)
            ->assertDontSee('data-js-cart-button', false);

        $this->withSession(['booking' => $booking])
            ->get(route('booking.staycationFood'))
            ->assertOk()
            ->assertSee('data-wishlist-key="food:', false)
            ->assertSee('>Save for later<', false);

        $this->withSession(['booking' => $booking])
            ->get(route('booking.staycationActivities'))
            ->assertOk()
            ->assertSee('data-wishlist-key="activity:', false)
            ->assertSee('>Save for later<', false);
    }

    public function test_authenticated_user_can_toggle_a_configured_room_without_duplicate_records(): void
    {
        $user = User::factory()->create();
        $room = $this->room();
        $payload = [
            'item_type' => 'staycation',
            'item_id' => $room->id,
            'selection' => [
                'room_id' => $room->id,
                'quantity' => 2,
                'adults' => 2,
                'children' => 1,
                'pets' => 'yes',
                'pet_agreement' => true,
                'num_pets' => 1,
                'price' => 1,
            ],
        ];

        $this->actingAs($user)->withSession(['booking' => $this->context])
            ->postJson(route('booking.wishlist.toggle'), $payload)
            ->assertOk()
            ->assertJson(['saved' => true, 'count' => 1]);

        $this->assertDatabaseCount('booking_wishlist_items', 1);
        $saved = BookingWishlistItem::firstOrFail();
        $this->assertSame(2, $saved->selection['quantity']);
        $this->assertSame(2, $saved->selection['adults']);
        $this->assertSame(1, $saved->selection['children']);
        $this->assertTrue($saved->selection['pets']);
        $this->assertTrue($saved->selection['pet_agreement']);
        $this->assertSame(1, $saved->selection['num_pets']);
        $this->assertArrayNotHasKey('price', $saved->selection);

        $this->actingAs($user)->withSession(['booking' => $this->context])
            ->postJson(route('booking.wishlist.toggle'), $payload)
            ->assertOk()
            ->assertJson(['saved' => false, 'count' => 0]);

        $this->assertDatabaseCount('booking_wishlist_items', 0);
    }

    public function test_room_saved_for_later_uses_neutral_booking_defaults(): void
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
        $this->assertSame(1, $saved->selection['adults']);
        $this->assertSame(0, $saved->selection['children']);
        $this->assertSame(1, $saved->selection['quantity']);
        $this->assertFalse($saved->selection['pets']);
        $this->assertSame(0, $saved->selection['num_pets']);
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
            ->assertSee('Room quantity, guests, and pets are configured in the Booking Cart before checkout.')
            ->assertSee('data-booking-wishlist-count', false);
    }

    public function test_wishlist_lists_first_saved_item_at_the_top(): void
    {
        $user = User::factory()->create();
        $firstFood = $this->food();
        $firstFood->update(['name' => 'First Saved Meal']);
        $secondFood = $this->food();
        $secondFood->update(['name' => 'Second Saved Meal']);

        $this->wishlistItem($user, 'food', $firstFood->id, ['quantity' => 1]);
        $this->wishlistItem($user, 'food', $secondFood->id, ['quantity' => 1]);

        $this->actingAs($user)
            ->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSeeInOrder(['First Saved Meal', 'Second Saved Meal']);
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

    public function test_saved_room_inputs_are_shown_in_wishlist_and_prefilled_in_booking_cart(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['base_quantity' => 4]);
        $saved = $this->wishlistItem($user, 'staycation', $room->id, [
            'room_id' => $room->id,
            'quantity' => 2,
            'adults' => 3,
            'children' => 1,
            'pets' => true,
            'pet_agreement' => true,
            'num_pets' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSee('data-wishlist-stay-summary', false)
            ->assertSee('2 Rooms')
            ->assertSee('3 Adults per room')
            ->assertSee('1 Child per room')
            ->assertSee('1 Pet per room');

        $this->post(route('booking.wishlist.add-to-cart'), ['items' => [$saved->id]])
            ->assertRedirect(route('booking.cart.index'))
            ->assertSessionHas('booking_cart.items.staycation:' . $room->id . '.selection.quantity', 2)
            ->assertSessionHas('booking_cart.items.staycation:' . $room->id . '.selection.adults', 3)
            ->assertSessionHas('booking_cart.items.staycation:' . $room->id . '.selection.children', 1)
            ->assertSessionHas('booking_cart.items.staycation:' . $room->id . '.selection.pets', true)
            ->assertSessionHas('booking_cart.items.staycation:' . $room->id . '.selection.num_pets', 1);

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('name="quantity" min="1" max="4" value="2"', false)
            ->assertSee('name="adults" min="1" max="4" value="3"', false)
            ->assertSee('name="children" min="0" max="2" value="1"', false)
            ->assertSee('border border-gray-400 bg-white', false)
            ->assertSee('x-data="{ pets: \'yes\' }"', false)
            ->assertSee('name="num_pets" min="1" max="2" value="1"', false)
            ->assertSee("\$dispatch('open-pet-policy')", false)
            ->assertSee('data-pet-policy-modal', false)
            ->assertSee('Owner Responsibility')
            ->assertDontSee('target="_blank"', false);
    }

    public function test_wishlist_and_booking_cart_include_additional_adults_and_pets_in_room_total(): void
    {
        $user = User::factory()->create();
        $room = $this->room([
            'name' => 'Standard Glamping',
            'price' => 6800,
            'included_guest_capacity' => 4,
            'max_guest_capacity' => 6,
            'adults' => 4,
            'children' => 2,
        ]);
        BookingSetting::create([
            'key' => 'mattress_price_per_night',
            'label' => 'Additional adult per night',
            'value' => 1200,
            'category' => 'pricing',
        ]);
        BookingSetting::create([
            'key' => 'pet_price',
            'label' => 'Pet fee',
            'value' => 2000,
            'category' => 'pricing',
        ]);
        $context = ['checkin' => '2026-09-10', 'checkout' => '2026-09-11'];
        $saved = $user->bookingWishlistItems()->create([
            'item_type' => 'staycation',
            'item_id' => $room->id,
            'context_hash' => hash('sha256', '2026-09-10|2026-09-11'),
            'booking_context' => $context,
            'selection' => [
                'room_id' => $room->id,
                'quantity' => 1,
                'adults' => 6,
                'children' => 2,
                'pets' => true,
                'pet_agreement' => true,
                'num_pets' => 2,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('booking.wishlist.index'))
            ->assertOk()
            ->assertSee('data-staycation-price-breakdown', false)
            ->assertSee('Accommodation')
            ->assertSee('Additional 2 adults per room')
            ->assertSee('2 Pets per room')
            ->assertSee('&#8369;13,200.00', false);

        $this->post(route('booking.wishlist.add-to-cart'), ['items' => [$saved->id]])
            ->assertRedirect(route('booking.cart.index'));

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('data-staycation-price-breakdown', false)
            ->assertSee('&#8369;6,800.00', false)
            ->assertSee('&#8369;2,400.00', false)
            ->assertSee('&#8369;4,000.00', false)
            ->assertSee('&#8369;13,200.00', false);

        $key = 'staycation:' . $room->id;
        $this->patch(route('booking.cart.staycation.update', $key), [
            'quantity' => 2,
            'adults' => 6,
            'children' => 2,
            'pets' => 'yes',
            'pet_agreement' => 1,
            'num_pets' => 2,
        ])->assertRedirect();

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('Accommodation (2 rooms)')
            ->assertSee('&#8369;13,600.00', false)
            ->assertSee('&#8369;4,800.00', false)
            ->assertSee('&#8369;8,000.00', false)
            ->assertSee('&#8369;26,400.00', false);
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
            ->assertSee('Configure your stay')
            ->assertSee('Defaults: 1 room, 1 adult, 0 children, no pets.')
            ->assertSee('data-booking-cart-count', false)
            ->assertSee('>2</span>', false);
    }

    public function test_customer_can_configure_room_quantity_guests_and_pets_in_booking_cart(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['base_quantity' => 3]);
        $key = 'staycation:' . $room->id;
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                $key => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => [
                        'room_id' => $room->id,
                        'quantity' => 1,
                        'adults' => 1,
                        'children' => 0,
                        'pets' => false,
                        'pet_agreement' => false,
                        'num_pets' => 0,
                    ],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->patch(route('booking.cart.staycation.update', $key), [
                'quantity' => 2,
                'adults' => 2,
                'children' => 1,
                'pets' => 'yes',
                'pet_agreement' => 1,
                'num_pets' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Room details updated.')
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.quantity', 2)
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.adults', 2)
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.children', 1)
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.pets', true)
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.pet_agreement', true)
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.num_pets', 1);

        $this->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSee('Room quantity')
            ->assertSee('Adults per room')
            ->assertSee('Children per room')
            ->assertSee('Pets per room')
            ->assertSee('₱20,000.00');
    }

    public function test_room_cart_requires_pet_policy_agreement_when_pets_are_selected(): void
    {
        $user = User::factory()->create();
        $room = $this->room();
        $key = 'staycation:' . $room->id;
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                $key => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => ['room_id' => $room->id, 'quantity' => 1, 'adults' => 1, 'children' => 0, 'pets' => false],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->patch(route('booking.cart.staycation.update', $key), [
                'quantity' => 1,
                'adults' => 1,
                'children' => 0,
                'pets' => 'yes',
                'pet_agreement' => 0,
                'num_pets' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Please agree to the Pet Policy before adding pets.')
            ->assertSessionHas('booking_cart.items.' . $key . '.selection.pets', false);
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

    public function test_booking_cart_always_lists_rooms_before_foods_and_activities(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['name' => 'First Room']);
        $food = $this->food();
        $food->update(['name' => 'Second Food']);
        $activity = Activity::create([
            'name' => 'Third Activity',
            'category' => 'adventure',
            'price' => 800,
            'is_active' => true,
        ]);
        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                'activity:' . $activity->id => [
                    'item_type' => 'activity',
                    'item_id' => $activity->id,
                    'booking_context' => $this->context,
                    'selection' => ['guests' => 1],
                ],
                'food:' . $food->id => [
                    'item_type' => 'food',
                    'item_id' => $food->id,
                    'booking_context' => $this->context,
                    'selection' => ['quantity' => 1],
                ],
                'staycation:' . $room->id => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => [
                        'room_id' => $room->id,
                        'quantity' => 1,
                        'adults' => 2,
                        'children' => 0,
                        'pets' => false,
                    ],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->get(route('booking.cart.index'))
            ->assertOk()
            ->assertSeeInOrder(['First Room', 'Second Food', 'Third Activity']);
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

    public function test_cart_checkout_hydrates_meal_package_items_and_lists_package_before_individual_foods(): void
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

        $includedFood = $this->food();
        $includedFood->update(['name' => 'Calaia Garden Salad', 'price' => 300]);
        $choiceFood = $this->food();
        $choiceFood->update(['name' => 'Continental Platter', 'price' => 500]);
        $individualFood = $this->food();
        $individualFood->update(['name' => 'Filipino Platter', 'price' => 500]);
        $package = FoodPackage::create([
            'name' => 'Set A',
            'price' => 2000,
            'category' => 'package',
            'is_active' => true,
            'sections' => [
                [
                    'title' => 'Lunch',
                    'mode' => 'included',
                    'items' => [['food_id' => $includedFood->id, 'quantity' => 1]],
                ],
                [
                    'title' => 'Big Breakfast',
                    'mode' => 'choice',
                    'choice_limit' => 1,
                    'items' => [['food_id' => $choiceFood->id, 'quantity' => 1]],
                ],
            ],
        ]);
        $package->foods()->attach([
            $includedFood->id => ['quantity' => 1],
            $choiceFood->id => ['quantity' => 1],
        ]);

        $cart = [
            'context_hash' => hash('sha256', '2026-09-10|2026-09-12'),
            'booking_context' => $this->context,
            'items' => [
                'food:' . $individualFood->id => [
                    'item_type' => 'food',
                    'item_id' => $individualFood->id,
                    'booking_context' => $this->context,
                    'selection' => ['quantity' => 1],
                ],
                'food_package:' . $package->id => [
                    'item_type' => 'food_package',
                    'item_id' => $package->id,
                    'booking_context' => $this->context,
                    'selection' => [
                        'quantity' => 1,
                        'selected_choices' => [[
                            'section_title' => 'Big Breakfast',
                            'section_index' => 1,
                            'food_id' => (string) $choiceFood->id,
                            'food_name' => 'Continental Platter',
                            'price' => 500,
                        ]],
                    ],
                ],
                'staycation:' . $room->id => [
                    'item_type' => 'staycation',
                    'item_id' => $room->id,
                    'booking_context' => $this->context,
                    'selection' => ['room_id' => $room->id, 'quantity' => 1, 'adults' => 2, 'children' => 0, 'pets' => false],
                ],
            ],
        ];

        $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->post(route('booking.cart.checkout'))
            ->assertRedirect(route('booking.staycationPayment'));

        $selectedFoods = session('booking.selected_foods');
        $this->assertSame(['Set A', 'Filipino Platter'], array_column($selectedFoods, 'name'));
        $this->assertSame(
            ['Calaia Garden Salad', 'Continental Platter'],
            array_column($selectedFoods[0]['included_foods'], 'name')
        );
        $this->assertStringNotContainsString('Unknown', json_encode($selectedFoods));
        $this->assertSame(2500.0, (float) session('booking.final_totals.food_total'));

        $staleSelectedFoods = $selectedFoods;
        $staleSelectedFoods[0]['included_foods'] = [[
            'id' => $includedFood->id,
            'name' => 'Unknown',
            'price' => 0,
            'quantity' => 1,
        ]];
        session(['booking.selected_foods' => $staleSelectedFoods]);

        $this->get(route('booking.staycationPayment'))
            ->assertOk()
            ->assertDontSee('Unknown')
            ->assertSeeInOrder(['Set A', 'Calaia Garden Salad', 'Continental Platter', 'Filipino Platter']);
    }

    public function test_room_quantity_expands_to_real_room_instances_before_checkout(): void
    {
        $user = User::factory()->create();
        $room = $this->room(['base_quantity' => 3]);
        foreach (['2026-09-10', '2026-09-11'] as $date) {
            DB::table('staycation_inventories')->insert([
                'staycation_id' => $room->id,
                'date' => $date,
                'available_quantity' => 3,
                'total_quantity' => 3,
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
                    'selection' => [
                        'room_id' => $room->id,
                        'quantity' => 2,
                        'adults' => 2,
                        'children' => 1,
                        'pets' => true,
                        'pet_agreement' => true,
                        'num_pets' => 1,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->withSession(['booking_cart' => $cart])
            ->post(route('booking.cart.checkout'));

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHas('booking.rooms_count', 2);
        $response->assertSessionHas('booking.adults_total', 4);
        $response->assertSessionHas('booking.children_total', 2);
        $accommodations = session('booking.accommodations');
        $this->assertCount(2, $accommodations);
        $this->assertSame([1, 1], array_column($accommodations, 'quantity'));
        $this->assertSame([1, 1], array_column($accommodations, 'num_pets'));
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
