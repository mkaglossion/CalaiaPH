<?php

namespace Tests\Feature;

use App\Models\Booking;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class BookingPaymentFailureTest extends TestCase
{
    use RefreshDatabase;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_failed_paymongo_callback_closes_pending_booking(): void
    {
        config()->set('services.paymongo.secret_key', 'sk_test_123');

        $booking = Booking::create([
            'transaction_id' => 'TXN-FAIL-1',
            'customer_name' => 'Failed Guest',
            'customer_email' => 'failed@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'confirmed',
            'paymongo_checkout_id' => 'cs_fail_123',
        ]);

        Cache::put('paymongo_checkout_reference:cs_fail_123', 'TXN-FAIL-1', now()->addHour());
        Cache::put('paymongo_checkout_context:TXN-FAIL-1', ['type' => 'staycation'], now()->addHour());

        $client = Mockery::mock('overload:GuzzleHttp\Client');
        $client->shouldReceive('get')
            ->once()
            ->andReturn(new Response(200, [], json_encode([
                'data' => [
                    'attributes' => [
                        'payment_intent' => [
                            'status' => 'awaiting_payment_method',
                            'last_payment_error' => 'cancelled',
                        ],
                    ],
                ],
            ])));

        $response = $this->get(route('booking.payment.failed', ['checkout_id' => 'cs_fail_123']));

        $response->assertRedirect(route('booking.payment'));

        $booking->refresh();

        $this->assertSame('failed', $booking->payment_status);
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('failed', data_get($booking->booking_data, 'payment_attempt.state'));
        $this->assertSame('paymongo_failed_callback', data_get($booking->booking_data, 'payment_attempt.reason'));
        $this->assertNull(Cache::get('paymongo_checkout_reference:cs_fail_123'));
        $this->assertNull(Cache::get('paymongo_checkout_context:TXN-FAIL-1'));
    }
}
