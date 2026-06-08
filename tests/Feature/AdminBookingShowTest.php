<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
