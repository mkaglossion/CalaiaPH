<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageBooking;
use App\Services\PaymongoWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymongoWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_session_paid_event_envelope_marks_package_booking_paid(): void
    {
        Mail::fake();

        $package = Package::create([
            'name' => 'Farm Tour',
            'description' => 'Package test',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        $booking = PackageBooking::create([
            'booking_reference' => 'PB-TEST123',
            'package_id' => $package->id,
            'booking_date' => '2026-06-10',
            'total_guests' => 2,
            'groups_booked' => 1,
            'first_name' => 'Mark',
            'last_name' => 'Test',
            'phone' => '9997826850',
            'email' => 'mark@example.com',
            'total_amount' => 2500,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'paymongo_reference' => 'cs_test_123',
        ]);

        $payload = [
            'data' => [
                'id' => 'evt_test_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_test_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'PB-TEST123',
                            'metadata' => [
                                'booking_id' => $booking->id,
                                'booking_reference' => 'PB-TEST123',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_test_123',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 250000,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue(PaymongoWebhookService::handleWebhook($payload));

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('pay_test_123', $booking->payment_reference);
    }
}
