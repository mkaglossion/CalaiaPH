<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExpireStalePendingBookingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_expires_only_stale_pending_paymongo_bookings(): void
    {
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
