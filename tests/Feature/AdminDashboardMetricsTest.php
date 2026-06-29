<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Models\Booking;
use App\Models\Food;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

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

        Booking::create($this->bookingAttributes('TXN-PAID-1', 'paid', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-PENDING-1', 'pending', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-FAILED-1', 'failed', 'cancelled'));

        $view = app(AdminDashboardController::class)->index(Request::create('/admin/dashboard', 'GET'));
        $data = $view->getData();

        $this->assertSame(1, $data['totalBookings']);
        $this->assertSame(1, $data['activeBookings']);
        $this->assertSame(1, $data['pendingBookings']);
        $this->assertSame(5000.0, $data['pendingRevenue']);
        $this->assertCount(1, $data['recentBookings']);
        $this->assertSame('TXN-PAID-1', $data['recentBookings']->first()->transaction_id);
    }

    public function test_dashboard_builds_sales_report_for_completed_and_pending_revenue(): void
    {
        Booking::create(array_merge($this->bookingAttributes('TXN-SALES-1', 'paid', 'confirmed'), [
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
            'total_amount' => 1000,
        ]));

        Booking::create(array_merge($this->bookingAttributes('TXN-SALES-3', 'pending', 'cancelled'), [
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
        $this->assertSame(2, $data['salesTotalTransactions']);
        $this->assertSame(4200.0, $data['salesTotalAmount']);
        $this->assertSame(3400.0, $data['salesPendingRevenue']);
        $this->assertCount(6, $data['salesReportCards']);
        $this->assertSame('Total Sales', $data['salesReportCards'][0]['label']);
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

    public function test_dashboard_booking_overview_includes_pending_attempts_in_the_selected_range(): void
    {
        Booking::create(array_merge($this->bookingAttributes('TXN-PENDING-OVERVIEW-1', 'pending', 'confirmed'), [
            'checkin_date' => now()->addDays(40)->toDateString(),
            'checkout_date' => now()->addDays(41)->toDateString(),
            'total_amount' => 2000,
        ]));

        $view = app(AdminDashboardController::class)->index(
            Request::create('/admin/dashboard', 'GET', [
                'range' => '30d',
            ])
        );

        $data = $view->getData();

        $this->assertSame(1, $data['pendingBookings']);
        $this->assertSame(1, collect($data['bookingOverviewChartItems'])->sum('pending'));
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
