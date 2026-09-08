<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminBookingsController;
use App\Models\Booking;
use App\Models\Staycation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminBookingsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_summarizes_every_room_and_guest_in_a_multi_room_staycation(): void
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

        Booking::create(array_merge($this->bookingAttributes('TXN-MULTI-ROOM', 'paid', 'confirmed'), [
            'booking_data' => [
                'accommodations' => [
                    $room->id => ['room_id' => $room->id, 'quantity' => 3, 'adults' => 2, 'children' => 1],
                    $room->id . '_2' => ['room_id' => $room->id, 'quantity' => 1, 'adults' => 1, 'children' => 0],
                ],
            ],
            'total_amount' => 40200,
        ]));

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET'));
        $booking = $view->getData()['bookings']->getCollection()->firstWhere('transaction_id', 'TXN-MULTI-ROOM');

        $this->assertSame('Premium Glamping x2', $booking->accommodation_summary);
        $this->assertSame(2, $booking->accommodation_unit_count);
        $this->assertSame(4, $booking->accommodation_guest_count);

        $html = $view->render();
        $this->assertStringContainsString('Premium Glamping x2', $html);
        $this->assertStringContainsString('2 rooms', $html);
        $this->assertStringContainsString('4 guests', $html);
    }

    public function test_index_supports_legacy_aggregated_rooms_and_missing_recovery_details(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        Booking::create(array_merge($this->bookingAttributes('TXN-LEGACY-ROOMS', 'paid', 'completed'), [
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
        Booking::create(array_merge($this->bookingAttributes('TXN-RECOVERED-NO-ROOM', 'paid', 'completed'), [
            'booking_data' => [
                'booking_type' => 'staycation',
                'recovered_from_paymongo' => true,
            ],
        ]));

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET'));
        $bookings = $view->getData()['bookings']->getCollection();
        $legacy = $bookings->firstWhere('transaction_id', 'TXN-LEGACY-ROOMS');
        $recovered = $bookings->firstWhere('transaction_id', 'TXN-RECOVERED-NO-ROOM');

        $this->assertSame('Premium Dome Tent x2', $legacy->accommodation_summary);
        $this->assertSame(2, $legacy->accommodation_unit_count);
        $this->assertSame(6, $legacy->accommodation_guest_count);
        $this->assertNull($recovered->accommodation_summary);

        $html = $view->render();
        $this->assertStringContainsString('Premium Dome Tent x2', $html);
        $this->assertStringContainsString('2 rooms', $html);
        $this->assertStringContainsString('6 guests', $html);
        $this->assertStringContainsString('Staycation', $html);
    }

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

    public function test_index_never_shows_an_expired_attempt_badge_for_a_paid_booking(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        Booking::create(array_merge(
            $this->bookingAttributes('TXN-PAID-WITH-STALE-ATTEMPT', 'paid', 'confirmed'),
            ['booking_data' => ['payment_attempt' => ['state' => 'expired']]]
        ));

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET'));
        $booking = $view->getData()['bookings']->getCollection()->firstWhere(
            'transaction_id',
            'TXN-PAID-WITH-STALE-ATTEMPT'
        );

        $this->assertSame('paid', $booking->effective_payment_status);
        $this->assertNull($booking->effective_attempt_state);

        $html = $view->render();
        $this->assertStringContainsString('Paid', $html);
        $this->assertStringNotContainsString('Expired Attempt', $html);
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

    public function test_index_reconciles_a_stale_paid_checkout_before_expiring_it(): void
    {
        config([
            'booking.payment_attempt_expiry_minutes' => 60,
            'services.paymongo.secret_key' => 'test-secret',
        ]);
        Mail::fake();
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_stale_paid' => Http::response([
                'data' => [
                    'attributes' => [
                        'payments' => [[
                            'id' => 'pay_stale_paid',
                            'attributes' => [
                                'status' => 'paid',
                                'amount' => 500000,
                            ],
                        ]],
                    ],
                ],
            ]),
        ]);

        $booking = Booking::create(array_merge(
            $this->bookingAttributes('TXN-STALE-PAID', 'pending', 'pending'),
            ['paymongo_checkout_id' => 'cs_stale_paid']
        ));
        $booking->forceFill(['created_at' => now()->subHours(2)])->save();

        $view = app(AdminBookingsController::class)->index(Request::create('/admin/bookings', 'GET', [
            'payment_scope' => 'operational',
        ]));

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', data_get($booking->booking_data, 'payment_attempt.state'));
        $this->assertSame(
            ['TXN-STALE-PAID'],
            $view->getData()['bookings']->getCollection()->pluck('transaction_id')->all()
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
