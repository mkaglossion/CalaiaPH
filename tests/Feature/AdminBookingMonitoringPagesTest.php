<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingMonitoringPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staycation_monitoring_page_renders_room_bookings_and_today_badges(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-STAY-001',
            'booking_type' => 'staycation',
            'booking_data' => [
                'accommodations' => [
                    [
                        'name' => 'Villa A',
                        'quantity' => 1,
                    ],
                ],
                'total_guests' => 4,
            ],
            'checkin_date' => today()->toDateString(),
            'checkout_date' => today()->addDay()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 6000,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.staycations'));

        $response->assertOk();
        $response->assertSee('Staycation Bookings');
        $response->assertSee('TXN-STAY-001');
        $response->assertSee('Villa A');
        $response->assertSee('Today');
    }

    public function test_staycation_monitoring_export_downloads_csv(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-STAY-CSV',
            'booking_type' => 'staycation',
            'booking_data' => [
                'accommodations' => [
                    [
                        'name' => 'Villa Export',
                        'quantity' => 1,
                    ],
                ],
                'total_guests' => 2,
            ],
            'checkin_date' => today()->toDateString(),
            'checkout_date' => today()->addDay()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 6500,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.staycations.export'));

        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=staycation-bookings-',
            $response->baseResponse->headers->get('content-disposition')
        );

        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame([
            'Reference',
            'Guest Name',
            'Contact Number',
            'Email',
            'Booking Date',
            'Stay Dates',
            'Room Type',
            'Guests',
            'Total Amount',
            'Payment Status',
            'Booking Status',
            'Monitoring Status',
        ], $rows[0]);
        $this->assertSame('TXN-STAY-CSV', $rows[1][0]);
        $this->assertSame('Villa Export', $rows[1][6]);
    }

    public function test_food_monitoring_page_renders_food_orders(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-FOOD-001',
            'booking_type' => 'staycation',
            'booking_data' => [
                'selected_foods' => [
                    [
                        'name' => 'Burger Set',
                        'quantity' => 2,
                        'date' => today()->toDateString(),
                    ],
                ],
            ],
            'checkin_date' => today()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 1500,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.foodandbeverage'));

        $response->assertOk();
        $response->assertSee('Food & Beverage Bookings');
        $response->assertSee('TXN-FOOD-001');
        $response->assertSee('Burger Set');
        $response->assertSee('Today');
    }

    public function test_food_monitoring_export_downloads_csv(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-FOOD-CSV',
            'booking_type' => 'staycation',
            'booking_data' => [
                'selected_foods' => [
                    [
                        'name' => 'Burger Set',
                        'quantity' => 3,
                        'date' => today()->toDateString(),
                    ],
                ],
            ],
            'checkin_date' => today()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 1500,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.foodandbeverage.export', [
            'search' => 'TXN-FOOD-CSV',
        ]));

        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=food-and-beverage-bookings-',
            $response->baseResponse->headers->get('content-disposition')
        );

        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame([
            'Reference',
            'Guest Name',
            'Contact Number',
            'Email',
            'Booking Date',
            'Food Items',
            'Qty',
            'Serving Date',
            'Total Amount',
            'Payment Status',
            'Booking Status',
            'Monitoring Status',
        ], $rows[0]);
        $this->assertSame('TXN-FOOD-CSV', $rows[1][0]);
        $this->assertSame('Burger Set', $rows[1][5]);
    }

    public function test_activity_monitoring_page_renders_activity_orders(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-ACT-001',
            'booking_type' => 'dayvisit',
            'booking_data' => [
                'selected_activities' => [
                    [
                        'name' => 'Kayak Ride',
                        'guests' => 4,
                        'date' => today()->toDateString(),
                    ],
                ],
                'selected_activity_packages' => [
                    [
                        'name' => 'Adventure Bundle',
                        'quantity' => 1,
                        'date' => today()->toDateString(),
                    ],
                ],
            ],
            'checkin_date' => today()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 2500,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.activities'));

        $response->assertOk();
        $response->assertSee('Activities Bookings');
        $response->assertSee('TXN-ACT-001');
        $response->assertSee('Kayak Ride');
        $response->assertSee('Adventure Bundle');
        $response->assertSee('Today');
    }

    public function test_activity_monitoring_export_downloads_csv(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-ACT-CSV',
            'booking_type' => 'dayvisit',
            'booking_data' => [
                'selected_activities' => [
                    [
                        'name' => 'Kayak Ride',
                        'guests' => 4,
                        'date' => today()->toDateString(),
                    ],
                ],
            ],
            'checkin_date' => today()->toDateString(),
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 2500,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.activities.export'));

        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=activities-bookings-',
            $response->baseResponse->headers->get('content-disposition')
        );

        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame([
            'Reference',
            'Guest Name',
            'Contact Number',
            'Email',
            'Booking Date',
            'Activities',
            'Pax',
            'Schedule',
            'Total Amount',
            'Payment Status',
            'Booking Status',
            'Monitoring Status',
        ], $rows[0]);
        $this->assertSame('TXN-ACT-CSV', $rows[1][0]);
        $this->assertSame('Kayak Ride', $rows[1][5]);
    }

    private function bookingAttributes(array $overrides): array
    {
        return array_merge([
            'transaction_id' => 'TXN-DEFAULT',
            'customer_name' => 'Monitoring Guest',
            'customer_email' => 'monitoring@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => today()->toDateString(),
            'checkout_date' => today()->addDay()->toDateString(),
            'booking_data' => [],
            'total_amount' => 1000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ], $overrides);
    }

    private function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];

        return array_values(array_filter(array_map(function (string $line): array {
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line) ?? $line;

            return str_getcsv($line);
        }, $lines), fn (array $row) => $row !== []));
    }
}
