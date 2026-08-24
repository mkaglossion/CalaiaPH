<?php

namespace Tests\Feature;

use App\Contracts\AddOnCheckoutGateway;
use App\Models\AddOnTransaction;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Food;
use App\Models\User;
use App\Services\PaymongoWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CustomerBookingAddOnTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_lists_only_the_authenticated_users_bookings_and_indicates_add_ons(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $booking = $this->createBooking($user, 'BK-2026-00123');
        $otherBooking = $this->createBooking($otherUser, 'BK-2026-00999');

        $this->createAddOn($booking, 'AO-2026-00001', 500);
        $this->createAddOn($otherBooking, 'AO-2026-00999', 900);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee('BK-2026-00123');
        $response->assertSee('1 add-on');
        $response->assertDontSee('BK-2026-00999');
        $response->assertDontSee('AO-2026-00999');
        $response->assertSee('href="' . route('booking.calendar') . '"', false);
        $response->assertSee('View Details &amp; Add Meals or Activities', false);
    }

    public function test_booking_details_show_add_ons_separately_without_changing_original_total(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');

        AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-00001',
            'type' => 'mixed',
            'items' => [
                ['type' => 'activity', 'name' => 'Kayaking', 'quantity' => 1, 'unit_price' => 500],
                ['type' => 'meal', 'name' => 'Meal', 'quantity' => 2, 'unit_price' => 150],
            ],
            'total_amount' => 800,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'paymongo',
            'payment_reference' => 'pay_addon_1',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('profile.bookings.show', $booking));

        $response->assertOk();
        $response->assertSee('AO-2026-00001');
        $response->assertSee('Kayaking');
        $response->assertSee('Meal');
        $response->assertSee('Original Booking Total');
        $response->assertSee('5,000.00');
        $response->assertSee('Add-ons Total');
        $response->assertSee('800.00');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'total_amount' => 5000,
            'payment_status' => 'paid',
        ]);
    }

    public function test_another_customer_cannot_open_the_booking_or_its_add_on_url(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $booking = $this->createBooking($owner, 'BK-2026-00123');
        $addOn = $this->createAddOn($booking, 'AO-2026-00001', 500);

        $this->actingAs($otherUser)
            ->get(route('profile.bookings.show', $booking))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('profile.bookings.add-ons.show', [$booking, $addOn]))
            ->assertForbidden();
    }

    public function test_an_add_on_from_a_different_booking_cannot_be_accessed_through_an_owned_booking(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $ownedBooking = $this->createBooking($user, 'BK-2026-00123');
        $otherBooking = $this->createBooking($otherUser, 'BK-2026-00999');
        $otherAddOn = $this->createAddOn($otherBooking, 'AO-2026-00999', 900);

        $this->actingAs($user)
            ->get(route('profile.bookings.add-ons.show', [$ownedBooking, $otherAddOn]))
            ->assertNotFound();
    }

    public function test_customer_can_create_a_separate_add_on_checkout_for_a_paid_booking(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $food = Food::create([
            'name' => 'Farm Breakfast',
            'category' => 'breakfast',
            'price' => 150,
            'is_active' => true,
            'stock_quantity' => 10,
            'is_inventory_managed' => true,
        ]);
        $activity = Activity::create([
            'name' => 'Kayaking',
            'price' => 500,
            'is_active' => true,
        ]);

        $checkoutGateway = Mockery::mock(AddOnCheckoutGateway::class);
        $checkoutGateway->shouldReceive('create')
            ->once()
            ->andReturn((object) [
                'id' => 'cs_addon_1',
                'checkout_url' => 'https://checkout.example/addon-1',
            ]);
        $this->app->instance(AddOnCheckoutGateway::class, $checkoutGateway);

        $response = $this->actingAs($user)->post(route('profile.bookings.add-ons.store', $booking), [
            'foods' => [$food->id => 2],
            'activities' => [$activity->id => 1],
        ]);

        $response->assertRedirect('https://checkout.example/addon-1');
        $addOn = AddOnTransaction::query()->firstOrFail();
        $this->assertSame($booking->id, $addOn->booking_id);
        $this->assertSame('AO-2026-00001', $addOn->reference);
        $this->assertSame('800.00', $addOn->total_amount);
        $this->assertSame('pending', $addOn->payment_status);
        $this->assertSame('cs_addon_1', $addOn->paymongo_checkout_id);
        $this->assertSame('https://checkout.example/addon-1', $addOn->checkout_url);
        $this->assertNotNull($addOn->expires_at);
        $this->assertTrue($addOn->expires_at->isFuture());
        $this->assertCount(2, $addOn->items);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'total_amount' => 5000]);
    }

    public function test_customer_can_resume_a_pending_add_on_payment_from_booking_details(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-00001',
            'type' => 'activity',
            'items' => [[
                'type' => 'activity',
                'item_id' => 1,
                'name' => 'Kayaking',
                'quantity' => 1,
                'unit_price' => 500,
                'total_price' => 500,
            ]],
            'total_amount' => 500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'paymongo_checkout_id' => 'cs_addon_pending',
            'checkout_url' => 'https://checkout.example/addon-pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($user)
            ->get(route('profile.bookings.show', $booking))
            ->assertOk()
            ->assertSee('AO-2026-00001')
            ->assertSee('Continue to Separate Payment')
            ->assertSee('Payment expires in');

        $this->actingAs($user)
            ->get(route('profile.bookings.add-ons.pay', [$booking, $addOn]))
            ->assertRedirect('https://checkout.example/addon-pending');
    }

    public function test_pending_add_on_expires_after_sixty_minutes_and_disappears_from_booking_details(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-EXPIRED',
            'type' => 'activity',
            'items' => [[
                'type' => 'activity',
                'item_id' => 1,
                'name' => 'Kayaking',
                'quantity' => 1,
                'unit_price' => 500,
                'total_price' => 500,
            ]],
            'total_amount' => 500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'paymongo_checkout_id' => 'cs_addon_expired',
            'checkout_url' => 'https://checkout.example/addon-expired',
            'expires_at' => now()->subSecond(),
        ]);

        $this->actingAs($user)
            ->get(route('profile.bookings.show', $booking))
            ->assertOk()
            ->assertDontSee('AO-2026-EXPIRED')
            ->assertDontSee('https://checkout.example/addon-expired');

        $this->assertDatabaseHas('add_on_transactions', [
            'id' => $addOn->id,
            'status' => 'expired',
            'payment_status' => 'expired',
        ]);
    }

    public function test_legacy_pending_add_on_recovers_its_checkout_url_when_payment_is_resumed(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_example']);
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-LEGACY',
            'type' => 'activity',
            'items' => [['type' => 'activity', 'name' => 'Kayaking', 'quantity' => 1, 'unit_price' => 500]],
            'total_amount' => 500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'paymongo_checkout_id' => 'cs_addon_legacy',
            'expires_at' => now()->addMinutes(30),
        ]);

        Http::fake([
            'api.paymongo.com/v1/checkout_sessions/cs_addon_legacy' => Http::response([
                'data' => [
                    'id' => 'cs_addon_legacy',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.example/addon-legacy',
                        'payment_status' => 'unpaid',
                    ],
                ],
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('profile.bookings.show', $booking))
            ->assertOk()
            ->assertSee('Continue to Separate Payment');

        $this->actingAs($user)
            ->get(route('profile.bookings.add-ons.pay', [$booking, $addOn]))
            ->assertRedirect('https://checkout.example/addon-legacy');

        $this->assertDatabaseHas('add_on_transactions', [
            'id' => $addOn->id,
            'checkout_url' => 'https://checkout.example/addon-legacy',
        ]);
    }

    public function test_another_customer_cannot_resume_an_owned_pending_add_on_payment(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $booking = $this->createBooking($owner, 'BK-2026-00123');
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-00001',
            'type' => 'activity',
            'items' => [['type' => 'activity', 'name' => 'Kayaking', 'quantity' => 1, 'unit_price' => 500]],
            'total_amount' => 500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'checkout_url' => 'https://checkout.example/addon-pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($otherUser)
            ->get(route('profile.bookings.add-ons.pay', [$booking, $addOn]))
            ->assertForbidden();
    }

    public function test_add_on_selection_cards_show_dynamic_food_and_activity_images(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $food = Food::create([
            'name' => 'Farm Breakfast',
            'category' => 'breakfast',
            'price' => 150,
            'image' => 'https://cdn.example.test/food.jpg',
            'is_active' => true,
            'stock_quantity' => 10,
            'is_inventory_managed' => true,
        ]);
        $activity = Activity::create([
            'name' => 'Kayaking',
            'price' => 500,
            'image' => 'https://cdn.example.test/activity.jpg',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('profile.bookings.add-ons.create', $booking))
            ->assertOk()
            ->assertSee($food->image, false)
            ->assertSee($activity->image, false)
            ->assertSee('alt="Farm Breakfast"', false)
            ->assertSee('alt="Kayaking"', false);
    }

    public function test_customer_cannot_add_items_to_a_completed_stay(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-PAST');
        $booking->update([
            'checkin_date' => today()->subDays(3)->toDateString(),
            'checkout_date' => today()->subDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('profile.bookings.add-ons.create', $booking))
            ->assertStatus(422);
    }

    public function test_success_callback_confirms_add_on_and_reduces_meal_inventory_only_once(): void
    {
        config(['services.paymongo.secret_key' => 'sk_test_example']);
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $food = Food::create([
            'name' => 'Farm Breakfast',
            'category' => 'breakfast',
            'price' => 150,
            'is_active' => true,
            'stock_quantity' => 5,
            'is_inventory_managed' => true,
        ]);
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-00001',
            'type' => 'meal',
            'items' => [[
                'type' => 'meal',
                'item_id' => $food->id,
                'name' => $food->name,
                'quantity' => 2,
                'unit_price' => 150,
                'total_price' => 300,
            ]],
            'total_amount' => 300,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'paymongo_checkout_id' => 'cs_addon_1',
        ]);

        Http::fake([
            'api.paymongo.com/v1/checkout_sessions/cs_addon_1' => Http::response([
                'data' => [
                    'id' => 'cs_addon_1',
                    'attributes' => [
                        'payment_status' => 'paid',
                        'payments' => [[
                            'id' => 'pay_addon_1',
                            'attributes' => ['amount' => 30000],
                        ]],
                    ],
                ],
            ]),
        ]);

        $url = route('profile.add-ons.payment.success', [
            'add_on' => $addOn->id,
            'checkout_id' => 'cs_addon_1',
        ]);

        $this->actingAs($user)->get($url)->assertRedirectContains(route('profile.bookings.show', $booking));
        $this->actingAs($user)->get($url)->assertRedirectContains(route('profile.bookings.show', $booking));

        $this->assertDatabaseHas('add_on_transactions', [
            'id' => $addOn->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => 'pay_addon_1',
        ]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock_quantity' => 3]);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'total_amount' => 5000]);
    }

    public function test_paymongo_webhook_confirms_the_linked_add_on_without_touching_original_booking_total(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $booking = $this->createBooking($user, 'BK-2026-00123');
        $addOn = AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => 'AO-2026-00001',
            'type' => 'activity',
            'items' => [[
                'type' => 'activity',
                'item_id' => 1,
                'name' => 'Kayaking',
                'quantity' => 1,
                'unit_price' => 500,
                'total_price' => 500,
            ]],
            'total_amount' => 500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'paymongo_checkout_id' => 'cs_addon_webhook',
        ]);

        $handled = PaymongoWebhookService::handleWebhook([
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_addon_webhook',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'AO-2026-00001',
                            'metadata' => [
                                'record_type' => 'add_on',
                                'add_on_transaction_id' => $addOn->id,
                                'booking_id' => $booking->id,
                            ],
                            'payments' => [[
                                'id' => 'pay_addon_webhook',
                                'attributes' => ['amount' => 50000],
                            ]],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($handled);
        $this->assertDatabaseHas('add_on_transactions', [
            'id' => $addOn->id,
            'payment_status' => 'paid',
            'payment_reference' => 'pay_addon_webhook',
        ]);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'total_amount' => 5000]);
    }

    private function createBooking(User $user, string $reference): Booking
    {
        return Booking::create([
            'user_id' => $user->id,
            'transaction_id' => $reference,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->mobile_number,
            'booking_type' => 'staycation',
            'checkin_date' => today()->addDays(7)->toDateString(),
            'checkout_date' => today()->addDays(9)->toDateString(),
            'booking_data' => [
                'accommodations' => [['name' => 'Family Tent', 'quantity' => 1]],
            ],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    private function createAddOn(Booking $booking, string $reference, float $amount): AddOnTransaction
    {
        return AddOnTransaction::create([
            'booking_id' => $booking->id,
            'reference' => $reference,
            'type' => 'activity',
            'items' => [
                ['type' => 'activity', 'name' => 'Kayaking', 'quantity' => 1, 'unit_price' => $amount],
            ],
            'total_amount' => $amount,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'paymongo',
            'paid_at' => now(),
        ]);
    }
}
