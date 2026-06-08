<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminBookingsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_hides_payment_attempts_by_default(): void
    {
        Booking::create($this->bookingAttributes('TXN-PAID-1', 'paid', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-PENDING-1', 'pending', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-FAILED-1', 'failed', 'cancelled'));

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET'));
        $bookings = $view->getData()['bookings']->getCollection();

        $this->assertCount(1, $bookings);
        $this->assertSame(['TXN-PAID-1'], $bookings->pluck('transaction_id')->all());
    }

    public function test_index_can_show_payment_attempts_when_requested(): void
    {
        Booking::create($this->bookingAttributes('TXN-PAID-1', 'paid', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-PENDING-1', 'pending', 'confirmed'));
        Booking::create($this->bookingAttributes('TXN-FAILED-1', 'failed', 'cancelled'));

        $attemptsView = app(AdminBookingsController::class)->index(
            Request::create('/admin/bookings', 'GET', ['payment_scope' => 'attempts'])
        );
        $attempts = $attemptsView->getData()['bookings']->getCollection();

        $this->assertEqualsCanonicalizing(
            ['TXN-FAILED-1', 'TXN-PENDING-1'],
            $attempts->pluck('transaction_id')->all()
        );

        $failedView = app(AdminBookingsController::class)->index(
            Request::create('/admin/bookings', 'GET', ['payment_status' => 'failed'])
        );
        $failed = $failedView->getData()['bookings']->getCollection();

        $this->assertSame(['TXN-FAILED-1'], $failed->pluck('transaction_id')->all());
    }

    private function bookingAttributes(string $transactionId, string $paymentStatus, string $status): array
    {
        return [
            'transaction_id' => $transactionId,
            'customer_name' => 'Test Guest',
            'customer_email' => 'guest@example.com',
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
