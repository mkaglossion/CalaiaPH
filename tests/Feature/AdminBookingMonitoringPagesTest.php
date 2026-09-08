<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageBooking;
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

    public function test_staycation_monitoring_page_summarizes_all_room_instances_and_guests(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $room = \App\Models\Staycation::create([
            'name' => 'Premium Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 4,
            'capacity' => 4,
            'adults' => 4,
            'children' => 2,
            'price' => 10050,
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-MULTI-ROOM',
            'booking_type' => 'staycation',
            'booking_data' => [
                'accommodations' => [
                    $room->id => ['room_id' => $room->id, 'quantity' => 3, 'adults' => 2, 'children' => 1],
                    $room->id . '_2' => ['room_id' => $room->id, 'quantity' => 1, 'adults' => 1, 'children' => 0],
                ],
            ],
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'total_amount' => 40200,
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.staycations'));

        $response->assertOk();
        $response->assertSeeText('Premium Glamping x2');
        $response->assertSee('<div class="text-sm text-gray-900">4</div>', false);
    }

    public function test_staycation_monitoring_page_supports_legacy_aggregated_room_data(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-LEGACY-ROOMS',
            'booking_type' => 'staycation',
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
            'status' => 'completed',
            'payment_status' => 'paid',
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.staycations'));

        $response->assertOk();
        $response->assertSeeText('Premium Dome Tent x2');
        $response->assertSee('<div class="text-sm text-gray-900">6</div>', false);
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

    public function test_package_monitoring_page_renders_only_package_bookings(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $package = Package::create([
            'name' => 'Garden Getaway',
            'price' => 4500,
            'max_guests' => 8,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        PackageBooking::create([
            'booking_reference' => 'PB-MONITOR-001',
            'package_id' => $package->id,
            'booking_date' => today()->toDateString(),
            'session' => 'morning',
            'total_guests' => 4,
            'first_name' => 'Package',
            'last_name' => 'Guest',
            'email' => 'package@example.com',
            'phone' => '09171234567',
            'total_amount' => 4500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-NOT-PACKAGE',
        ]));

        $response = $this->actingAs($admin)->get(route('admin.bookings.packages'));

        $response->assertOk();
        $response->assertSee('Package Bookings');
        $response->assertSee('PB-MONITOR-001');
        $response->assertSee('Garden Getaway');
        $response->assertSee('Morning');
        $response->assertSee('Today');
        $response->assertDontSee('TXN-NOT-PACKAGE');
    }

    public function test_package_monitoring_export_downloads_csv(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $package = Package::create([
            'name' => 'Farm Tour',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        PackageBooking::create([
            'booking_reference' => 'PB-MONITOR-CSV',
            'package_id' => $package->id,
            'booking_date' => today()->toDateString(),
            'total_guests' => 2,
            'first_name' => 'CSV',
            'last_name' => 'Guest',
            'email' => 'csv@example.com',
            'phone' => '09170000000',
            'total_amount' => 2500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bookings.packages.export'));

        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=package-bookings-',
            $response->baseResponse->headers->get('content-disposition')
        );

        $rows = $this->parseCsv($response->streamedContent());
        $this->assertSame([
            'Reference',
            'Guest Name',
            'Contact Number',
            'Email',
            'Booking Date',
            'Package Date',
            'Package',
            'Guests',
            'Total Amount',
            'Payment Status',
            'Booking Status',
            'Monitoring Status',
        ], $rows[0]);
        $this->assertSame('PB-MONITOR-CSV', $rows[1][0]);
        $this->assertSame('Farm Tour', $rows[1][6]);
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
