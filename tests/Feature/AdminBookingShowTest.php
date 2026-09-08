<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminBookingShowTest extends TestCase
{
    use RefreshDatabase;

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
