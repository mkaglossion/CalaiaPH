<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use App\Models\Staycation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminBookingShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_labels_normal_accommodation_entries_as_separate_rooms(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $room = Staycation::create([
            'name' => 'Premium Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 4,
            'capacity' => 4,
            'adults' => 4,
            'children' => 2,
            'price' => 10050,
        ]);

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'booking_type' => 'staycation',
            'checkout_date' => '2026-06-12',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'booking_data' => [
                'accommodations' => [
                    $room->id => ['room_id' => $room->id, 'quantity' => 3, 'adults' => 2, 'children' => 1, 'total_price' => 20100],
                    $room->id . '_2' => ['room_id' => $room->id, 'quantity' => 1, 'adults' => 1, 'children' => 0, 'total_price' => 20100],
                ],
            ],
            'total_amount' => 40200,
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $this->assertStringContainsString('Room 1 of 2', $html);
        $this->assertStringContainsString('Room 2 of 2', $html);
        $this->assertStringNotContainsString('Quantity:', $html);
    }

    public function test_show_labels_legacy_aggregated_quantity_as_rooms(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'booking_type' => 'staycation',
            'payment_status' => 'paid',
            'status' => 'completed',
            'booking_data' => [
                'adults' => 4,
                'children' => 2,
                'accommodations' => [[
                    'name' => 'Premium Dome Tent',
                    'price' => 6800,
                    'quantity' => 2,
                    'subtotal' => 13600,
                ]],
            ],
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $this->assertStringContainsString('Premium Dome Tent', $html);
        $this->assertStringContainsString('Rooms:', $html);
        $this->assertStringNotContainsString('Quantity:', $html);
    }

    public function test_show_renders_staycation_price_breakdown(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $room = Staycation::create([
            'name' => 'Premium Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 3,
            'capacity' => 4,
            'max_guest_capacity' => 6,
            'included_guest_capacity' => 4,
            'adults' => 4,
            'children' => 2,
            'price' => 0,
        ]);

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'booking_type' => 'staycation',
            'checkin_date' => '2026-11-15',
            'checkout_date' => '2026-11-17',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'booking_data' => [
                'checkin' => '2026-11-15',
                'checkout' => '2026-11-17',
                'accommodations' => [[
                    'room_id' => $room->id,
                    'accommodation_id' => $room->id,
                    'accommodation_price' => 18400,
                    'additional_adults' => 1,
                    'additional_adult_price' => 2500,
                    'pet_price' => 0,
                    'total_price' => 20900,
                ]],
            ],
            'total_amount' => 20900,
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $this->assertStringContainsString('Price breakdown', $html);
        $this->assertStringContainsString('Premium Glamping - Nov 15, 2026', $html);
        $this->assertStringContainsString('Extra guest/mattress (1 guest x 2 nights)', $html);
        $this->assertStringContainsString('Recorded amount breakdown', $html);
        $this->assertStringContainsString('None · &#8369;0.00', $html);
        $this->assertStringContainsString('&#8369;20,900.00', $html);
    }

    public function test_show_normalizes_selected_packages_foods_and_activities_for_view(): void
    {
        $booking = Booking::create([
            'transaction_id' => 'TXN-SHOW-1',
            'customer_name' => 'View Guest',
            'customer_email' => 'view@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'dayvisit',
            'checkin_date' => '2026-06-10',
            'checkout_date' => null,
            'booking_data' => [
                'packages' => [
                    [
                        'name' => 'Family Day Tour',
                        'guests' => 4,
                        'quantity' => 2,
                        'price_per_person' => 350,
                        'total_price' => 2800,
                    ],
                ],
                'selected_foods' => [
                    ['name' => 'Pork BBQ', 'quantity' => 3, 'price' => 120],
                ],
                'selected_activities' => [
                    ['name' => 'Kayak', 'guests' => 2, 'price' => 300],
                ],
                'selected_activity_packages' => [
                    ['name' => 'Adventure Bundle', 'quantity' => 1, 'price' => 900],
                ],
            ],
            'total_amount' => 4360,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $view = app(AdminBookingsController::class)->show($booking->id);
        $data = $view->getData();

        $this->assertSame('Family Day Tour', $data['selectedPackages'][0]['name']);
        $this->assertSame(2800.0, $data['selectedPackages'][0]['total_price']);
        $this->assertSame('Pork BBQ', $data['foodItems'][0]['name']);
        $this->assertSame(360.0, $data['foodItems'][0]['total_price']);
        $this->assertSame('Kayak', $data['singleActivities'][0]['name']);
        $this->assertSame(600.0, $data['singleActivities'][0]['total_price']);
        $this->assertSame('Adventure Bundle', $data['activityPackages'][0]['name']);
        $this->assertSame(900.0, $data['activityPackages'][0]['total_price']);
    }

    public function test_paid_booking_display_ignores_a_stale_expired_attempt(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'booking_data' => [
                'payment_attempt' => ['state' => 'expired'],
            ],
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $this->assertStringContainsString('Paid', $html);
        $this->assertStringNotContainsString('Pending/Expired', $html);
        $this->assertStringNotContainsString('Expired Attempt', $html);
    }

    public function test_show_displays_optional_social_contact_information(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'booking_data' => [
                'booking_contact' => [
                    'social_contact_method' => 'facebook_messenger',
                    'social_contact_account' => 'm.me/payment.guest',
                ],
            ],
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $this->assertStringContainsString('Preferred Social / Messaging Contact', $html);
        $this->assertStringContainsString('Facebook Messenger:', $html);
        $this->assertStringContainsString('m.me/payment.guest', $html);
    }

    public function test_show_reconciles_a_pending_booking_with_paymongo(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        config(['services.paymongo.secret_key' => 'test-secret']);
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_paid_30' => Http::response([
                'data' => [
                    'attributes' => [
                        'payments' => [[
                            'id' => 'pay_paid_30',
                            'attributes' => [
                                'status' => 'paid',
                                'amount' => 450000,
                            ],
                        ]],
                    ],
                ],
            ]),
        ]);

        $booking = Booking::create(array_merge($this->bookingAttributes(), [
            'paymongo_checkout_id' => 'cs_paid_30',
            'booking_data' => [
                'payment_attempt' => ['state' => 'pending'],
            ],
        ]));

        $html = app(AdminBookingsController::class)->show($booking->id)->render();

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', data_get($booking->booking_data, 'payment_attempt.state'));
        $this->assertStringContainsString('Paid', $html);
        $this->assertStringNotContainsString('Pending/Expired', $html);
    }

    private function bookingAttributes(): array
    {
        return [
            'transaction_id' => 'TXN-SHOW-' . uniqid(),
            'customer_name' => 'Payment Guest',
            'customer_email' => 'payment@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'dayvisit',
            'checkin_date' => '2026-06-10',
            'checkout_date' => null,
            'booking_data' => [],
            'total_amount' => 4500,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ];
    }
}
