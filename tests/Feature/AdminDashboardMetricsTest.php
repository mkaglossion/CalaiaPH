<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Models\Booking;
use App\Models\Food;
use App\Models\Package;
use App\Models\PackageBooking;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-22 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_excludes_pending_and_failed_payment_attempts_from_operational_metrics(): void
    {
        User::factory()->create(['user_type' => 'client']);
        Product::create([
            'title' => 'Product A',
            'description' => 'Inventory test product',
            'price' => 100,
            'image' => 'product-a.jpg',
            'category' => 'fresh',
            'is_active' => true,
            'stock_quantity' => 20,
            'is_inventory_managed' => true,
        ]);
        Food::create([
            'name' => 'Food A',
            'description' => 'Inventory test food',
            'price' => 100,
            'stock_quantity' => 20,
            'is_inventory_managed' => true,
        ]);
        Product::create([
            'title' => 'Inactive Product',
            'description' => 'Inactive inventory item',
            'price' => 100,
            'image' => 'inactive-product.jpg',
            'category' => 'fresh',
            'is_active' => false,
            'stock_quantity' => 0,
            'is_inventory_managed' => true,
        ]);
        Food::create([
            'name' => 'Inactive Food',
            'description' => 'Inactive inventory food',
            'price' => 100,
            'is_active' => false,
            'stock_quantity' => 0,
            'is_inventory_managed' => true,
        ]);

        $serviceDates = [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
        ];

        $paid = Booking::create(array_merge($this->bookingAttributes('TXN-PAID-1', 'paid', 'confirmed'), $serviceDates));
        $pending = Booking::create(array_merge($this->bookingAttributes('TXN-PENDING-1', 'pending', 'confirmed'), $serviceDates));
        $failed = Booking::create(array_merge($this->bookingAttributes('TXN-FAILED-1', 'failed', 'cancelled'), $serviceDates));
        $this->setCreatedAt($paid, now()->subMinute());
        $this->setCreatedAt($pending, now()->subMinutes(2));
        $this->setCreatedAt($failed, now()->subMinutes(3));

        $view = app(AdminDashboardController::class)->index(Request::create('/admin/dashboard', 'GET'));
        $data = $view->getData();

        $this->assertSame(1, $data['totalBookings']);
        $this->assertSame(1, $data['activeBookings']);
        $this->assertSame(1, $data['pendingBookings']);
        $this->assertSame(0, $data['lowStockProducts']);
        $this->assertSame(0, $data['lowStockFoods']);
        $this->assertCount(3, $data['recentBookings']);
        $this->assertSame('TXN-PAID-1', $data['recentBookings']->first()->transaction_id);
    }

    public function test_dashboard_reconciles_a_paid_paymongo_checkout_before_rendering_recent_bookings(): void
    {
        config(['services.paymongo.secret_key' => 'test-secret']);
        Mail::fake();
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_dashboard_paid' => Http::response([
                'data' => [
                    'attributes' => [
                        'paid_at' => now()->timestamp,
                        'payments' => [[
                            'id' => 'pay_dashboard_paid',
                            'attributes' => [
                                'status' => 'paid',
                                'amount' => 4020000,
                            ],
                        ]],
                    ],
                ],
            ]),
        ]);

        $booking = Booking::create(array_merge(
            $this->bookingAttributes('TXN-DASHBOARD-PAID', 'pending', 'pending'),
            [
                'customer_name' => 'Katrina Rodriguez',
                'total_amount' => 40200,
                'paymongo_checkout_id' => 'cs_dashboard_paid',
            ]
        ));

        $view = app(AdminDashboardController::class)->index(Request::create('/admin/dashboard', 'GET'));

        $booking->refresh();
        $recentCard = collect($view->getData()['recentBookingCards'])
            ->firstWhere('name', 'Katrina Rodriguez');

        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('Confirmed', $recentCard['status'] ?? null);
    }

    public function test_dashboard_builds_paid_sales_report_and_live_pending_value(): void
    {
        Booking::create(array_merge($this->bookingAttributes('TXN-SALES-1', 'paid', 'confirmed'), [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'booking_data' => [
                'final_totals' => [
                    'accommodation_total' => 3000,
                    'food_total' => 500,
                    'activities_total' => 700,
                ],
                'selected_foods' => [
                    [
                        'name' => 'Burger Set',
                        'quantity' => 1,
                        'price' => 500,
                    ],
                ],
                'selected_activities' => [
                    [
                        'name' => 'Kayak Ride',
                        'guests' => 1,
                        'price' => 700,
                    ],
                ],
            ],
            'total_amount' => 4200,
        ]));

        Booking::create(array_merge($this->bookingAttributes('TXN-SALES-2', 'pending', 'pending'), [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'total_amount' => 1000,
        ]));

        Booking::create(array_merge($this->bookingAttributes('TXN-SALES-3', 'pending', 'cancelled'), [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'total_amount' => 2400,
        ]));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => '7d',
                'sales_view' => 'weekly',
            ])
        );
        $data = $view->getData();

        $this->assertSame('weekly', $data['salesView']);
        $this->assertSame('Sales trend', $data['salesTrendLabel']);
        $this->assertSame(1, $data['salesTotalTransactions']);
        $this->assertSame(4200.0, $data['salesTotalAmount']);
        $this->assertSame(1000.0, $data['salesPendingRevenue']);
        $this->assertCount(6, $data['salesReportCards']);
        $this->assertSame('Net Paid Sales', $data['salesReportCards'][0]['label']);
        $this->assertSame(1, $data['salesReportCards'][0]['count']);
        $this->assertSame(1, $data['salesReportCards'][1]['count']);
        $this->assertSame(1, $data['salesReportCards'][2]['count']);
        $this->assertSame(1, $data['salesReportCards'][3]['count']);
        $this->assertCount(3, $data['salesDistributionSeries']);
        $this->assertSame('Staycation', $data['salesDistributionSeries'][0]->label);
        $this->assertSame('Food & Beverage', $data['salesDistributionSeries'][1]->label);
        $this->assertSame('Activities', $data['salesDistributionSeries'][2]->label);
        $this->assertNotEmpty($data['salesReportSeries']);
    }

    public function test_revenue_range_uses_service_dates_for_regular_and_package_bookings(): void
    {
        $package = $this->createPackage('service-date');

        $regularInRange = Booking::create(array_merge(
            $this->bookingAttributes('TXN-SERVICE-IN-RANGE', 'paid', 'confirmed'),
            [
                'checkin_date' => '2026-07-03',
                'checkout_date' => '2026-07-04',
                'total_amount' => 1000,
            ]
        ));
        $this->setCreatedAt($regularInRange, Carbon::parse('2026-06-10 09:00:00'));

        $packageInRange = $this->createPackageBooking(
            $package,
            'PKG-SERVICE-IN-RANGE',
            '2026-07-04',
            'paid',
            'confirmed',
            2000
        );
        $this->setCreatedAt($packageInRange, Carbon::parse('2026-06-11 09:00:00'));

        $regularOutsideRange = Booking::create(array_merge(
            $this->bookingAttributes('TXN-SERVICE-OUTSIDE-RANGE', 'paid', 'confirmed'),
            [
                'checkin_date' => '2026-08-03',
                'checkout_date' => '2026-08-04',
                'total_amount' => 9000,
            ]
        ));
        $this->setCreatedAt($regularOutsideRange, Carbon::parse('2026-07-03 09:00:00'));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => 'custom',
                'from' => '2026-07-01',
                'to' => '2026-07-07',
            ])
        );
        $data = $view->getData();

        $this->assertSame(3000.0, $data['netRevenue']);
        $this->assertSame(3000.0, $data['paidRevenue']);
        $this->assertSame(3000.0, $data['totalRevenue']);
        $this->assertSame(3000.0, (float) collect($data['revenueSummarySeries'])->sum('net'));
        $this->assertSame(3000.0, $data['salesTotalAmount']);
    }

    public function test_revenue_follows_payment_state_and_adds_refunds_back_for_gross(): void
    {
        $serviceDates = [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
        ];

        Booking::create(array_merge(
            $this->bookingAttributes('TXN-NET-PAID', 'paid', 'confirmed'),
            $serviceDates,
            ['total_amount' => 3000]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-REFUND-CANCELLED', 'refunded', 'cancelled'),
            $serviceDates,
            ['total_amount' => 500]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-REFUND-COMPLETED', 'refunded', 'completed'),
            $serviceDates,
            ['total_amount' => 250]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-PAID-CANCELLED', 'paid', 'cancelled'),
            $serviceDates,
            ['total_amount' => 700]
        ));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', ['range' => 'today'])
        );
        $data = $view->getData();
        $refundCard = collect($data['salesCategoryCards'])->firstWhere('label', 'Refund Amount');

        $this->assertSame(3700.0, $data['netRevenue']);
        $this->assertSame(3700.0, $data['paidRevenue']);
        $this->assertSame(750.0, $data['refundedRevenue']);
        $this->assertSame(4450.0, $data['grossRevenue']);
        $this->assertSame(3700.0, $data['totalRevenue']);
        $this->assertSame(750.0, (float) $refundCard->amount);
        $this->assertSame(2, (int) $refundCard->count);
    }

    public function test_live_pending_revenue_excludes_failed_cancelled_completed_and_stale_attempts(): void
    {
        $serviceDates = [
            'checkin_date' => now()->addDay()->toDateString(),
            'checkout_date' => now()->addDays(2)->toDateString(),
        ];

        $livePending = Booking::create(array_merge(
            $this->bookingAttributes('TXN-PENDING-LIVE', 'pending', 'pending'),
            $serviceDates,
            ['total_amount' => 100]
        ));
        $this->setCreatedAt($livePending, now()->subMinutes(10));

        $failed = Booking::create(array_merge(
            $this->bookingAttributes('TXN-PENDING-FAILED', 'failed', 'cancelled'),
            $serviceDates,
            ['total_amount' => 200]
        ));
        $this->setCreatedAt($failed, now()->subMinutes(5));

        $cancelled = Booking::create(array_merge(
            $this->bookingAttributes('TXN-PENDING-CANCELLED', 'pending', 'cancelled'),
            $serviceDates,
            ['total_amount' => 300]
        ));
        $this->setCreatedAt($cancelled, now()->subMinutes(5));

        $stale = Booking::create(array_merge(
            $this->bookingAttributes('TXN-PENDING-STALE', 'pending', 'pending'),
            $serviceDates,
            ['total_amount' => 400]
        ));
        $this->setCreatedAt($stale, now()->subMinutes(1441));

        $completed = Booking::create(array_merge(
            $this->bookingAttributes('TXN-PENDING-COMPLETED', 'pending', 'completed'),
            $serviceDates,
            ['total_amount' => 500]
        ));
        $this->setCreatedAt($completed, now()->subMinutes(5));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => 'custom',
                'from' => '2026-07-01',
                'to' => '2026-07-07',
            ])
        );
        $data = $view->getData();
        $livePendingCard = collect($data['overviewCards'])->firstWhere('label', 'Live Pending');
        $pendingValueTile = collect($data['revenueTiles'])->firstWhere('label', 'Live Pending Value');

        $this->assertSame(1, $data['pendingBookings']);
        $this->assertSame(100.0, $data['pendingRevenue']);
        $this->assertSame(100.0, $data['salesPendingRevenue']);
        $this->assertSame('1', $livePendingCard['value']);
        $this->assertStringContainsString('100', $pendingValueTile['value']);
    }

    public function test_sales_categories_follow_paid_state_and_reconcile_to_total_charged(): void
    {
        $serviceDates = [
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
        ];

        Booking::create(array_merge(
            $this->bookingAttributes('TXN-CATEGORY-STAYCATION', 'paid', 'confirmed'),
            $serviceDates,
            [
                'booking_type' => 'staycation',
                'booking_data' => [
                    'final_totals' => [
                        'accommodation_total' => 3000,
                        'food_total' => 500,
                        'activities_total' => 500,
                    ],
                ],
                'subtotal' => 4000,
                'vat_amount' => 680,
                'total_amount' => 4480,
            ]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-CATEGORY-DAYVISIT', 'paid', 'confirmed'),
            $serviceDates,
            [
                'booking_type' => 'dayvisit',
                'booking_data' => [
                    'packages' => [
                        ['total_price' => 1000],
                    ],
                    'selected_activities' => [
                        ['name' => 'Kayak', 'quantity' => 1, 'price' => 200],
                    ],
                ],
                'total_amount' => 1344,
            ]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-CATEGORY-PENDING', 'pending', 'pending'),
            $serviceDates,
            ['total_amount' => 9000]
        ));
        Booking::create(array_merge(
            $this->bookingAttributes('TXN-CATEGORY-CANCELLED', 'paid', 'cancelled'),
            $serviceDates,
            ['total_amount' => 8000]
        ));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', ['range' => 'today'])
        );
        $data = $view->getData();
        $totalSalesCard = collect($data['salesReportCards'])->firstWhere('label', 'Net Paid Sales');
        $chargedCategoryTotal = (float) collect($data['salesCategoryCards'])
            ->reject(fn (object $card) => $card->label === 'Refund Amount')
            ->sum('amount');
        $trendCategoryTotal = (float) collect($data['salesTrendChartSeries'])->sum(
            fn (object $row) => (float) $row->staycation + (float) $row->food + (float) $row->activities
        );

        $this->assertSame(3, $totalSalesCard['count']);
        $this->assertSame(13824.0, $totalSalesCard['amount']);
        $this->assertSame(13824.0, $data['salesTotalAmount']);
        $this->assertEqualsWithDelta(13824.0, $chargedCategoryTotal, 0.01);
        $this->assertEqualsWithDelta(13824.0, $trendCategoryTotal, 0.01);
    }

    public function test_upcoming_card_counts_future_confirmed_paid_bookings_independent_of_past_range(): void
    {
        $package = $this->createPackage('upcoming');

        Booking::create(array_merge($this->bookingAttributes('TXN-UPCOMING-REGULAR', 'paid', 'confirmed'), [
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(4)->toDateString(),
        ]));
        $this->createPackageBooking(
            $package,
            'PKG-UPCOMING-PAID',
            now()->addDays(4)->toDateString(),
            'paid',
            'confirmed',
            2000
        );

        Booking::create(array_merge($this->bookingAttributes('TXN-UPCOMING-PENDING', 'pending', 'confirmed'), [
            'checkin_date' => now()->addDays(5)->toDateString(),
            'checkout_date' => now()->addDays(6)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-UPCOMING-REFUNDED', 'refunded', 'confirmed'), [
            'checkin_date' => now()->addDays(6)->toDateString(),
            'checkout_date' => now()->addDays(7)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-UPCOMING-CANCELLED', 'paid', 'cancelled'), [
            'checkin_date' => now()->addDays(7)->toDateString(),
            'checkout_date' => now()->addDays(8)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-PAST-CONFIRMED', 'paid', 'confirmed'), [
            'checkin_date' => '2026-07-03',
            'checkout_date' => '2026-07-04',
        ]));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => 'custom',
                'from' => '2026-07-01',
                'to' => '2026-07-07',
            ])
        );
        $data = $view->getData();
        $upcomingCard = collect($data['overviewCards'])->firstWhere('label', 'Upcoming');

        $this->assertSame(2, $data['upcomingBookings']);
        $this->assertSame('2', $upcomingCard['value']);
    }

    public function test_booking_overview_uses_mutually_exclusive_lifecycle_buckets_for_records_created_in_range(): void
    {
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-CONFIRMED', 'paid', 'confirmed'), [
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(4)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-COMPLETED-1', 'paid', 'completed'), [
            'checkin_date' => now()->subDays(3)->toDateString(),
            'checkout_date' => now()->subDays(2)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-COMPLETED-2', 'paid', 'completed'), [
            'checkin_date' => now()->subDays(2)->toDateString(),
            'checkout_date' => now()->subDay()->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-PENDING', 'pending', 'pending'), [
            'checkin_date' => now()->addDay()->toDateString(),
            'checkout_date' => now()->addDays(2)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-CLOSED', 'failed', 'cancelled'), [
            'checkin_date' => now()->addDays(5)->toDateString(),
            'checkout_date' => now()->addDays(6)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-PAID-CANCELLED', 'paid', 'cancelled'), [
            'checkin_date' => now()->addDays(6)->toDateString(),
            'checkout_date' => now()->addDays(7)->toDateString(),
        ]));
        Booking::create(array_merge($this->bookingAttributes('TXN-LIFECYCLE-REFUNDED', 'refunded', 'completed'), [
            'checkin_date' => now()->subDays(5)->toDateString(),
            'checkout_date' => now()->subDays(4)->toDateString(),
        ]));

        $outsideCreationRange = Booking::create(array_merge(
            $this->bookingAttributes('TXN-LIFECYCLE-OUTSIDE-CREATION-RANGE', 'paid', 'completed'),
            [
                'checkin_date' => now()->toDateString(),
                'checkout_date' => now()->addDay()->toDateString(),
            ]
        ));
        $this->setCreatedAt($outsideCreationRange, now()->subDay());

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => 'today',
                'booking_view' => 'daily',
            ])
        );
        $data = $view->getData();
        $todayRow = collect($data['bookingOverviewChartItems'])->firstWhere('key', now()->toDateString());

        $this->assertNotNull($todayRow);
        $this->assertSame(1, (int) $todayRow->upcoming);
        $this->assertSame(2, (int) $todayRow->completed);
        $this->assertSame(1, (int) $todayRow->active);
        $this->assertSame(3, (int) $todayRow->cancelled);
        $this->assertSame(7, (int) $todayRow->total);
        $this->assertSame($data['bookingTickStep'] * 4, $data['bookingAxisMax']);
        $this->assertGreaterThanOrEqual(3, $data['bookingAxisMax']);
        $this->assertSame(
            (int) $todayRow->total,
            (int) $todayRow->upcoming
                + (int) $todayRow->completed
                + (int) $todayRow->active
                + (int) $todayRow->cancelled
        );
    }

    public function test_export_has_nonblank_summary_and_correct_regular_and_package_breakdown(): void
    {
        $package = $this->createPackage('export');

        Booking::create(array_merge($this->bookingAttributes('TXN-EXPORT-GLAMPING', 'paid', 'confirmed'), [
            'booking_type' => 'glamping',
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'total_amount' => 1000,
        ]));
        $this->createPackageBooking(
            $package,
            'PKG-EXPORT-PAID',
            now()->toDateString(),
            'paid',
            'confirmed',
            2000
        );

        $response = app(AdminDashboardController::class)->export(
            Request::create('/admin/dashboard/export', 'GET', ['range' => 'today'])
        );
        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('<td>Report Range</td><td>Today</td>', $content);
        $this->assertStringContainsString('<td>Total Bookings</td><td>2</td>', $content);
        $this->assertStringContainsString('<td>Staycation Bookings</td><td>1</td>', $content);
        $this->assertStringContainsString('<td>Package Bookings</td><td>1</td>', $content);
    }

    public function test_range_labels_are_correct_and_unknown_ranges_fall_back_to_thirty_days(): void
    {
        $sevenDayData = app(AdminDashboardController::class)
            ->index(Request::create('/admin/dashboard', 'GET', ['range' => '7d']))
            ->getData();

        $this->assertSame('7d', $sevenDayData['range']);
        $this->assertSame('7 Days', $sevenDayData['rangeShortLabel']);
        $this->assertSame('Last 7 Days', $sevenDayData['rangeLabel']);

        $fallbackData = app(AdminDashboardController::class)
            ->index(Request::create('/admin/dashboard', 'GET', ['range' => 'unexpected']))
            ->getData();

        $this->assertSame('30d', $fallbackData['range']);
        $this->assertSame('30 Days', $fallbackData['rangeShortLabel']);
        $this->assertSame('Last 30 Days', $fallbackData['rangeLabel']);
    }

    private function createPackage(string $suffix): Package
    {
        return Package::create([
            'name' => "Dashboard Package {$suffix}",
            'description' => 'Package used by dashboard metric regression tests.',
            'price' => 2000,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
            'max_groups_per_day' => 5,
            'slug' => 'dashboard-package-' . strtolower($suffix),
            'duration_value' => 1,
            'duration_unit' => 'days',
        ]);
    }

    private function createPackageBooking(
        Package $package,
        string $reference,
        string $bookingDate,
        string $paymentStatus,
        string $status,
        float $totalAmount
    ): PackageBooking {
        return PackageBooking::create([
            'booking_reference' => $reference,
            'package_id' => $package->id,
            'booking_date' => $bookingDate,
            'groups_booked' => 1,
            'session' => 'morning',
            'total_guests' => 2,
            'first_name' => 'Package',
            'last_name' => 'Guest',
            'email' => strtolower($reference) . '@example.com',
            'phone' => '09171234567',
            'total_amount' => $totalAmount,
            'status' => $status,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function setCreatedAt(Booking|PackageBooking $booking, Carbon $createdAt): void
    {
        $booking->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();
    }

    private function bookingAttributes(string $transactionId, string $paymentStatus, string $status): array
    {
        return [
            'transaction_id' => $transactionId,
            'customer_name' => 'Dashboard Guest',
            'customer_email' => 'dashboard@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => $status,
        ];
    }
}
