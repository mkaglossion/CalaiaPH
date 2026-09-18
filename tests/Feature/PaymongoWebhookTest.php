<?php

namespace Tests\Feature;

use App\Mail\PackageBookingConfirmation;
use App\Models\Booking;
use App\Models\AdminSystemAlert;
use App\Models\Package;
use App\Models\PackageBooking;
use App\Models\PaymongoWebhookEvent;
use App\Models\ShopOrder;
use App\Services\PaymongoWebhookService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymongoWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_endpoint_accepts_a_valid_live_signature(): void
    {
        config(['services.paymongo.webhook_secret' => 'whsec_test_valid']);

        $payload = [
            'data' => [
                'id' => 'evt_signature_test',
                'type' => 'event',
                'attributes' => [
                    'type' => 'test.signature_check',
                    'livemode' => true,
                    'data' => [
                        'id' => 'test_signature_resource',
                        'type' => 'test_resource',
                        'attributes' => [],
                    ],
                ],
            ],
        ];
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp . '.' . $json, 'whsec_test_valid');

        $response = $this->call(
            'POST',
            '/api/paymongo/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te=,li={$signature}",
            ],
            $json
        );

        $response->assertOk()->assertJson([
            'success' => true,
            'handled' => false,
        ]);

        $event = PaymongoWebhookEvent::latest('id')->firstOrFail();
        $this->assertTrue($event->signature_valid);
        $this->assertSame(200, $event->http_status);
    }

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
        Mail::assertSent(PackageBookingConfirmation::class, fn (PackageBookingConfirmation $mail) => $mail->hasTo('mark@example.com'));
        Mail::assertNotQueued(PackageBookingConfirmation::class);

        $this->assertTrue(PaymongoWebhookService::handleWebhook($payload));
        Mail::assertSentCount(1);
    }

    public function test_package_booking_rejects_a_paid_amount_mismatch(): void
    {
        Mail::fake();

        $package = Package::create([
            'name' => 'Secure Farm Tour',
            'description' => 'Package payment validation test',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        $booking = PackageBooking::create([
            'booking_reference' => 'PB-AMOUNT1',
            'package_id' => $package->id,
            'booking_date' => '2026-06-10',
            'total_guests' => 2,
            'groups_booked' => 1,
            'first_name' => 'Secure',
            'last_name' => 'Customer',
            'phone' => '9997826850',
            'email' => 'secure@example.com',
            'total_amount' => 2500,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'paymongo_reference' => 'cs_package_amount_1',
        ]);

        $this->assertFalse(PaymongoWebhookService::markPackageBookingPaid(
            $booking,
            'pay_package_amount_1',
            100
        ));

        $booking->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('pending', $booking->payment_status);
        Mail::assertNothingSent();
    }

    public function test_pending_booking_rejects_underpayment_without_changing_the_total(): void
    {
        $booking = Booking::create([
            'transaction_id' => 'TXN-UNDERPAY1',
            'customer_name' => 'Secure Customer',
            'customer_email' => 'secure@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-06-10',
            'checkout_date' => '2026-06-11',
            'booking_data' => ['booking_type' => 'staycation'],
            'total_amount' => 8800,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        try {
            app(PaymentService::class)->confirmPendingBooking($booking, [
                'checkout_id' => 'cs_underpay_1',
                'payment_reference' => 'pay_underpay_1',
                'paid_amount_centavos' => 100,
            ]);
            $this->fail('Underpayment should not confirm a pending booking.');
        } catch (\UnexpectedValueException $e) {
            $this->assertSame('Verified payment amount does not match the booking total.', $e->getMessage());
        }

        $booking->refresh();
        $this->assertSame('pending', $booking->payment_status);
        $this->assertSame('pending', $booking->status);
        $this->assertSame(8800.0, (float) $booking->total_amount);
    }

    public function test_browser_cancel_callback_does_not_change_package_payment_state(): void
    {
        $package = Package::create([
            'name' => 'Cancellation Test Package',
            'description' => 'Cancellation callback test',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        $booking = PackageBooking::create([
            'booking_reference' => 'PB-CANCEL1',
            'package_id' => $package->id,
            'booking_date' => '2026-06-10',
            'total_guests' => 2,
            'groups_booked' => 1,
            'first_name' => 'Secure',
            'last_name' => 'Customer',
            'phone' => '9997826850',
            'email' => 'secure@example.com',
            'total_amount' => 2500,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'paymongo_reference' => 'cs_cancel_1',
        ]);

        $this->get(route('booking.payment-cancelled', ['checkout_id' => 'cs_cancel_1']))
            ->assertRedirect('/packages');

        $booking->refresh();
        $this->assertSame('pending_payment', $booking->status);
        $this->assertSame('pending', $booking->payment_status);
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

    public function test_shop_order_webhook_rejects_paid_amount_mismatch(): void
    {
        $order = ShopOrder::create([
            'order_reference' => 'SHOP-SECURE123',
            'customer_name' => 'Shop Customer',
            'customer_email' => 'shop@example.com',
            'customer_phone' => '09171234567',
            'cart_items' => [
                [
                    'title' => 'Calaia Tomato Pack',
                    'price' => 5000,
                    'quantity' => 1,
                    'subtotal' => 5000,
                ],
            ],
            'original_total' => 5000,
            'vat_amount' => 600,
            'final_total' => 5600,
            'delivery_option' => 'pickup',
            'pickup_date' => '2026-06-10',
            'payment_method' => 'paymongo',
            'transaction_id' => 'TXN-SHOP-SECURE123',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'paymongo_checkout_id' => 'cs_shop_secure_123',
        ]);

        $payload = [
            'data' => [
                'id' => 'evt_shop_amount_mismatch',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_shop_secure_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'TXN-SHOP-SECURE123',
                            'payments' => [
                                [
                                    'id' => 'pay_shop_wrong_amount',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 100,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertFalse(PaymongoWebhookService::handleWebhook($payload));
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_shop_order_webhook_marks_paid_when_amount_matches_order_total(): void
    {
        $order = ShopOrder::create([
            'order_reference' => 'SHOP-PAID123',
            'customer_name' => 'Shop Customer',
            'customer_email' => 'shop@example.com',
            'customer_phone' => '09171234567',
            'cart_items' => [
                [
                    'title' => 'Calaia Tomato Pack',
                    'price' => 5000,
                    'quantity' => 1,
                    'subtotal' => 5000,
                ],
            ],
            'original_total' => 5000,
            'vat_amount' => 600,
            'final_total' => 5600,
            'delivery_option' => 'pickup',
            'pickup_date' => '2026-06-10',
            'payment_method' => 'paymongo',
            'transaction_id' => 'TXN-SHOP-PAID123',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'paymongo_checkout_id' => 'cs_shop_paid_123',
        ]);

        $payload = [
            'data' => [
                'id' => 'evt_shop_amount_match',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_shop_paid_123',
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'TXN-SHOP-PAID123',
                            'payments' => [
                                [
                                    'id' => 'pay_shop_correct_amount',
                                    'type' => 'payment',
                                    'attributes' => [
                                        'amount' => 560000,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue(PaymongoWebhookService::handleWebhook($payload));
        $this->assertSame(ShopOrder::PAYMENT_STATUS_PAID, $order->fresh()->payment_status);
        $this->assertSame(ShopOrder::ORDER_STATUS_READY, $order->fresh()->order_status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_payment_refunded_event_marks_staycation_booking_refunded_by_payment_id(): void
    {
        $booking = Booking::create([
            'transaction_id' => 'TXN-REFUNDED123',
            'customer_name' => 'Refunded Guest',
            'customer_email' => 'refunded@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-09-24',
            'checkout_date' => '2026-09-25',
            'booking_data' => [],
            'total_amount' => 1500,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'payment_reference' => 'pay_refunded_123',
        ]);

        $payload = [
            'data' => [
                'id' => 'evt_refunded_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'payment.refunded',
                    'data' => [
                        'id' => 'pay_refunded_123',
                        'type' => 'payment',
                        'attributes' => [
                            'status' => 'refunded',
                            'amount' => 150000,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertTrue(PaymongoWebhookService::handleWebhook($payload));

        $booking->refresh();
        $this->assertSame('refunded', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
    }

    public function test_replay_command_applies_a_previously_unhandled_refund_event(): void
    {
        $booking = Booking::create([
            'transaction_id' => 'TXN-OLD-REFUND123',
            'customer_name' => 'Old Refunded Guest',
            'customer_email' => 'old-refund@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-09-24',
            'checkout_date' => '2026-09-25',
            'booking_data' => [],
            'total_amount' => 1500,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'payment_reference' => 'pay_old_refund_123',
        ]);

        $payload = [
            'data' => [
                'id' => 'evt_old_refund_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'payment.refunded',
                    'data' => [
                        'id' => 'pay_old_refund_123',
                        'type' => 'payment',
                        'attributes' => ['status' => 'refunded'],
                    ],
                ],
            ],
        ];

        $event = PaymongoWebhookEvent::create([
            'event_type' => 'payment.refunded',
            'resource_type' => 'payment',
            'reference' => 'TXN-OLD-REFUND123',
            'payment_id' => 'pay_old_refund_123',
            'signature_present' => true,
            'signature_valid' => true,
            'handled' => false,
            'http_status' => 200,
            'message' => 'Webhook processed',
            'payload' => $payload,
        ]);

        $this->artisan('paymongo:replay-unhandled', [
            '--reference' => 'pay_old_refund_123',
        ])->assertSuccessful();

        $this->assertSame('refunded', $booking->fresh()->payment_status);
        $this->assertTrue($event->fresh()->handled);
        $this->assertSame('Webhook replayed successfully', $event->fresh()->message);
    }

    public function test_payment_refunded_event_marks_package_booking_refunded_and_late_paid_event_does_not_revert_it(): void
    {
        $package = Package::create([
            'name' => 'Refunded Farm Tour',
            'description' => 'Package refund test',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);

        $booking = PackageBooking::create([
            'booking_reference' => 'PB-REFUND123',
            'package_id' => $package->id,
            'booking_date' => '2026-09-24',
            'total_guests' => 2,
            'groups_booked' => 1,
            'first_name' => 'Package',
            'last_name' => 'Guest',
            'phone' => '9997826850',
            'email' => 'package@example.com',
            'total_amount' => 2500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_reference' => 'pay_package_refund_123',
            'paymongo_reference' => 'cs_package_refund_123',
        ]);

        $refundPayload = [
            'data' => [
                'id' => 'evt_package_refund_123',
                'type' => 'event',
                'attributes' => [
                    'type' => 'payment.refunded',
                    'data' => [
                        'id' => 'pay_package_refund_123',
                        'type' => 'payment',
                        'attributes' => ['status' => 'refunded'],
                    ],
                ],
            ],
        ];

        $this->assertTrue(PaymongoWebhookService::handleWebhook($refundPayload));
        $booking->refresh();
        $this->assertSame('refunded', $booking->payment_status);

        $this->assertTrue(PaymongoWebhookService::markPackageBookingPaid($booking, 'pay_package_refund_123'));
        $this->assertSame('refunded', $booking->fresh()->payment_status);
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
