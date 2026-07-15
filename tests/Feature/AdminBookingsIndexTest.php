<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use Carbon\Carbon;
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

    public function test_attempt_date_filter_uses_attempt_created_date_and_includes_end_date(): void
    {
        $julyAttempt = Booking::create($this->bookingAttributes(
            'TXN-JULY-ATTEMPT',
            'failed',
            'cancelled',
            '2026-08-15'
        ));
        $julyAttempt->forceFill([
            'created_at' => Carbon::parse('2026-07-15 10:30:00'),
            'updated_at' => Carbon::parse('2026-07-15 10:30:00'),
        ])->save();

        $endDateAttempt = Booking::create($this->bookingAttributes(
            'TXN-END-DATE-ATTEMPT',
            'failed',
            'cancelled',
            '2026-09-01'
        ));
        $endDateAttempt->forceFill([
            'created_at' => Carbon::parse('2026-07-31 23:59:59'),
            'updated_at' => Carbon::parse('2026-07-31 23:59:59'),
        ])->save();

        $outsideAttempt = Booking::create($this->bookingAttributes(
            'TXN-AUGUST-ATTEMPT',
            'failed',
            'cancelled',
            '2026-07-10'
        ));
        $outsideAttempt->forceFill([
            'created_at' => Carbon::parse('2026-08-01 00:00:00'),
            'updated_at' => Carbon::parse('2026-08-01 00:00:00'),
        ])->save();

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET', [
            'payment_scope' => 'attempts',
            'payment_status' => 'failed',
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
            'page' => 2,
        ]));

        $this->assertEqualsCanonicalizing(
            ['TXN-JULY-ATTEMPT', 'TXN-END-DATE-ATTEMPT'],
            $view->getData()['bookings']->getCollection()->pluck('transaction_id')->all()
        );
    }

    public function test_explicit_operational_scope_and_exclude_cancelled_filter_match_dashboard_links(): void
    {
        $confirmed = Booking::create($this->bookingAttributes('TXN-CONFIRMED-OPERATIONAL', 'paid', 'confirmed', now()->toDateString()));
        $cancelled = Booking::create($this->bookingAttributes('TXN-CANCELLED-OPERATIONAL', 'paid', 'cancelled', now()->toDateString()));
        Booking::create($this->bookingAttributes('TXN-CONFIRMED-FAILED', 'failed', 'confirmed', now()->toDateString()));

        $upcomingView = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET', [
            'status' => 'confirmed',
            'payment_scope' => 'operational',
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $todayView = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET', [
            'payment_scope' => 'operational',
            'exclude_cancelled' => 1,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $this->assertSame(['TXN-CONFIRMED-OPERATIONAL'], $upcomingView->getData()['bookings']->getCollection()->pluck('transaction_id')->all());
        $this->assertEqualsCanonicalizing(
            ['TXN-CONFIRMED-OPERATIONAL'],
            $todayView->getData()['bookings']->getCollection()->pluck('transaction_id')->all()
        );
    }

    private function bookingAttributes(
        string $transactionId,
        string $paymentStatus,
        string $status,
        string $checkinDate = '2026-06-10'
    ): array
    {
        return [
            'transaction_id' => $transactionId,
            'customer_name' => 'Test Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => $checkinDate,
            'checkout_date' => Carbon::parse($checkinDate)->addDay()->toDateString(),
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => $status,
        ];
    }
}
