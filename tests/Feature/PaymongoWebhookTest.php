<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\AdminSystemAlert;
use App\Models\Package;
use App\Models\PackageBooking;
use App\Services\PaymongoWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_checkout_session_paid_event_can_confirm_existing_pending_booking_from_cached_reference(): void
    {
        $booking = Booking::create([
            'transaction_id' => 'TXN-CACHED123',
            'customer_name' => 'Cached Guest',
            'customer_email' => 'cached@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-06-10',
                'checkout' => '2026-06-11',
            ],
            'total_amount' => 8800,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'confirmed',
        ]);

        Cache::put('paymongo_checkout_reference:cs_cached_123', 'TXN-CACHED123', now()->addHour());
        Cache::put('paymongo_checkout_context:TXN-CACHED123', [
            'type' => 'staycation',
            'booking_data' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-06-10',
                'checkout' => '2026-06-11',
            ],
            'payment_data' => [
                'reference_id' => 'TXN-CACHED123',
                'expected_amount_centavos' => 880000,
                'email' => 'cached@example.com',
                'first_name' => 'Cached',
                'last_name' => 'Guest',
                'phone' => '09171234567',
            ],
        ], now()->addHour());

        $payload = [
            'data' => [
                'id' => 'evt_cached_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_cached_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'metadata' => [
                                'booking_type' => 'staycation',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_cached_123',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 880000,
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

        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('cs_cached_123', $booking->paymongo_checkout_id);
        $this->assertSame('pay_cached_123', $booking->payment_reference);
    }

    public function test_checkout_session_paid_event_rescue_create_preserves_staycation_dates_from_metadata(): void
    {
        $payload = [
            'data' => [
                'id' => 'evt_rescue_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_rescue_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'TXN-RESCUE123',
                            'metadata' => [
                                'booking_type' => 'glamping',
                                'staycation_category' => 'glamping',
                                'checkin_date' => '2026-06-15',
                                'checkout_date' => '2026-06-16',
                                'first_name' => 'Georgia',
                                'last_name' => 'Chu',
                                'email' => 'georgia@example.com',
                                'phone' => '09181234567',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_rescue_123',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 611000,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue(PaymongoWebhookService::handleWebhook($payload));

        $booking = Booking::where('transaction_id', 'TXN-RESCUE123')->firstOrFail();

        $this->assertSame('staycation', $booking->booking_type);
        $this->assertSame('2026-06-15', $booking->checkin_date->toDateString());
        $this->assertSame('2026-06-16', optional($booking->checkout_date)->toDateString());
        $this->assertSame('glamping', $booking->booking_data['staycation_category']);
    }

    public function test_payment_paid_event_without_booking_does_not_create_admin_alert(): void
    {
        $payload = [
            'data' => [
                'id' => 'evt_no_booking_payment_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => [
                        'id' => 'pay_no_booking_123',
                        'type' => 'payment',
                        'attributes' => [
                            'status' => 'paid',
                            'amount' => 1766000,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertFalse(PaymongoWebhookService::handleWebhook($payload));
        $this->assertSame(0, AdminSystemAlert::count());
    }

    public function test_checkout_payment_success_without_booking_does_not_create_admin_alert(): void
    {
        $payload = [
            'data' => [
                'id' => 'evt_no_booking_checkout_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_no_booking_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'payments' => [
                                [
                                    'id' => 'pay_no_booking_checkout_123',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 1766000,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertFalse(PaymongoWebhookService::handleWebhook($payload));
        $this->assertSame(0, AdminSystemAlert::count());
    }
}
