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
