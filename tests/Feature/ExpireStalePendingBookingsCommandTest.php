<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpireStalePendingBookingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_expires_only_stale_pending_paymongo_bookings(): void
    {
        config(['services.paymongo.secret_key' => 'test-secret']);

        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/*' => Http::response([
                'data' => ['attributes' => ['payments' => []]],
            ]),
        ]);

        $staleBooking = Booking::create($this->pendingBookingAttributes('TXN-STALE-1', 'cs_stale_1'));
        $staleBooking->forceFill([
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ])->saveQuietly();

        $freshBooking = Booking::create($this->pendingBookingAttributes('TXN-FRESH-1', 'cs_fresh_1'));

        Cache::put('paymongo_checkout_reference:cs_stale_1', 'TXN-STALE-1', now()->addHour());
        Cache::put('paymongo_checkout_context:TXN-STALE-1', ['type' => 'staycation'], now()->addHour());

        $this->artisan('bookings:expire-stale-pending', ['--minutes' => 60])
            ->expectsOutput('Expired 1 stale pending PayMongo booking(s) older than 60 minute(s).')
            ->assertSuccessful();

        $staleBooking->refresh();
        $freshBooking->refresh();

        $this->assertSame('failed', $staleBooking->payment_status);
        $this->assertSame('cancelled', $staleBooking->status);
        $this->assertSame('expired', data_get($staleBooking->booking_data, 'payment_attempt.state'));
        $this->assertSame('stale_timeout', data_get($staleBooking->booking_data, 'payment_attempt.reason'));
        $this->assertSame('pending', $freshBooking->payment_status);
        $this->assertSame('confirmed', $freshBooking->status);
        $this->assertNull(Cache::get('paymongo_checkout_reference:cs_stale_1'));
        $this->assertNull(Cache::get('paymongo_checkout_context:TXN-STALE-1'));
    }

    public function test_command_recovers_a_fresh_paid_checkout_without_waiting_for_expiry(): void
    {
        config(['services.paymongo.secret_key' => 'test-secret']);
        Mail::fake();
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_fresh_paid' => Http::response([
                'data' => [
                    'attributes' => [
                        'paid_at' => now()->timestamp,
                        'payments' => [[
                            'id' => 'pay_fresh_paid',
                            'attributes' => [
                                'status' => 'paid',
                                'amount' => 500000,
                            ],
                        ]],
                    ],
                ],
            ]),
        ]);

        $booking = Booking::create($this->pendingBookingAttributes('TXN-FRESH-PAID', 'cs_fresh_paid'));

        $this->artisan('bookings:expire-stale-pending', ['--minutes' => 1440])
            ->assertSuccessful();

        $booking->refresh();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', data_get($booking->booking_data, 'payment_attempt.state'));
        $this->assertSame('pay_fresh_paid', $booking->payment_reference);
        Mail::assertSent(BookingConfirmation::class, fn (BookingConfirmation $mail) => $mail->hasTo('pending@example.com'));
        Mail::assertNotQueued(BookingConfirmation::class);

        $this->artisan('bookings:expire-stale-pending', ['--minutes' => 1440])
            ->assertSuccessful();
        Mail::assertSentCount(1);
    }

    public function test_command_sends_confirmation_when_recovering_a_paid_day_visit(): void
    {
        config(['services.paymongo.secret_key' => 'test-secret']);
        Mail::fake();
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_dayvisit_paid' => Http::response([
                'data' => ['attributes' => ['paid_at' => now()->timestamp, 'payments' => [[
                    'id' => 'pay_dayvisit_paid',
                    'attributes' => ['status' => 'paid', 'amount' => 250000],
                ]]]],
            ]),
        ]);

        Booking::create(array_merge(
            $this->pendingBookingAttributes('TXN-DAYVISIT-PAID', 'cs_dayvisit_paid'),
            ['booking_type' => 'dayvisit']
        ));

        $this->artisan('bookings:expire-stale-pending', ['--minutes' => 1440])
            ->assertSuccessful();

        Mail::assertSent(BookingConfirmation::class, fn (BookingConfirmation $mail) =>
            $mail->hasTo('pending@example.com') && $mail->isDayvisit && !$mail->isStaycation
        );
        Mail::assertNotQueued(BookingConfirmation::class);
    }

    private function pendingBookingAttributes(string $transactionId, string $checkoutId): array
    {
        return [
            'transaction_id' => $transactionId,
            'customer_name' => 'Pending Guest',
            'customer_email' => 'pending@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'paymongo_checkout_id' => $checkoutId,
        ];
    }
}
