<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeOldPaymentAttemptsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_purges_only_old_failed_paymongo_attempts(): void
    {
        $oldAttempt = Booking::create($this->attemptAttributes('TXN-OLD-ATTEMPT'));
        $oldAttempt->forceFill([
            'created_at' => now()->subDays(40),
            'updated_at' => now()->subDays(40),
        ])->saveQuietly();

        $recentAttempt = Booking::create($this->attemptAttributes('TXN-RECENT-ATTEMPT'));
        $paidBooking = Booking::create([
            'transaction_id' => 'TXN-PAID-BOOKING',
            'customer_name' => 'Paid Guest',
            'customer_email' => 'paid@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $this->artisan('bookings:purge-old-attempts', ['--days' => 30])
            ->expectsOutput('Purged 1 old PayMongo payment attempt(s) older than 30 day(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('bookings', ['id' => $oldAttempt->id]);
        $this->assertDatabaseHas('bookings', ['id' => $recentAttempt->id]);
        $this->assertDatabaseHas('bookings', ['id' => $paidBooking->id]);
    }

    private function attemptAttributes(string $transactionId): array
    {
        return [
            'transaction_id' => $transactionId,
            'customer_name' => 'Attempt Guest',
            'customer_email' => 'attempt@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [
                'payment_attempt' => [
                    'record_type' => 'payment_attempt',
                    'state' => 'expired',
                    'reason' => 'stale_timeout',
                ],
            ],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ];
    }
}
