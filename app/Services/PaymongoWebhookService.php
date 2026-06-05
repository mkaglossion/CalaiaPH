<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\PackageInventory;
use App\Models\AdminSystemAlert;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PackageBookingConfirmation;

class PaymongoWebhookService
{
    /**
     * Handle PayMongo webhook events
     */
    public static function handleWebhook(array $payload)
    {
        $normalized = self::normalizeWebhookPayload($payload);
        $eventType = $normalized['event_type'];
        $data = $normalized['resource'];

        Log::info("PayMongo webhook received", [
            'type' => $eventType,
            'data_type' => $data['type'] ?? null,
        ]);

        if (!$eventType) {
            Log::warning("PayMongo webhook missing type");
            return false;
        }

        return match($eventType) {
            'payment.paid' => self::handlePaymentPaid($data),
            'payment.failed' => self::handlePaymentFailed($data),
            'checkout_session.payment_failed' => self::handleCheckoutPaymentFailed($data),
            'checkout_session.payment_success' => self::handleCheckoutPaymentSuccess($data),
            // Current PayMongo naming used in checkout webhooks
            'checkout_session.payment.paid' => self::handleCheckoutPaymentSuccess($data),
            'checkout_session.payment.failed' => self::handleCheckoutPaymentFailed($data),
            default => self::handleUnknownEvent($eventType, $data),
        };
    }

    /**
     * Normalize PayMongo webhook payloads so event envelopes and direct
     * resource payloads resolve to the same structure.
     */
    public static function normalizeWebhookPayload(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        $resource = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        if (($resource['type'] ?? null) === 'event') {
            $eventAttributes = is_array($resource['attributes'] ?? null) ? $resource['attributes'] : [];
            $eventType = $eventAttributes['type'] ?? $eventType;
            $resource = is_array($eventAttributes['data'] ?? null) ? $eventAttributes['data'] : [];
        }

        return [
            'event_type' => $eventType,
            'resource' => $resource,
        ];
    }

    private static function handlePaymentPaid(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $metadata = is_array($attributes['metadata'] ?? null) ? $attributes['metadata'] : [];
        $paymentId = $data['id'] ?? null;
        $status = $attributes['status'] ?? null;
        $amount = $attributes['amount'] ?? 0;
        $billing = is_array($attributes['billing'] ?? null) ? $attributes['billing'] : [];
        $referenceNumber = $attributes['reference'] ?? null;
        $referenceNumber = $referenceNumber ?: ($attributes['reference_number'] ?? null);
        $referenceNumber = $referenceNumber
            ?: ($metadata['reference'] ?? null)
            ?: ($metadata['reference_id'] ?? null)
            ?: ($metadata['reference_number'] ?? null)
            ?: ($metadata['booking_reference'] ?? null);
        $checkoutSessionId = $attributes['checkout_session_id'] ?? null;

        Log::info("PayMongo payment paid event", [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'reference' => $referenceNumber,
        ]);

        if ($status === 'paid') {
            if (self::markShopOrderPaid($referenceNumber, $checkoutSessionId, $paymentId, $amount)) {
                return true;
            }

            // Try existing Booking flow first (transaction_id or checkout_session_id)
            $booking = null;
            if ($referenceNumber) {
                $booking = self::findBookingByReference($referenceNumber);
            }
            if (!$booking && $checkoutSessionId) {
                $booking = Booking::where('paymongo_checkout_id', $checkoutSessionId)
                    ->where('payment_status', 'pending')
                    ->first();
            }
            if ($booking instanceof Booking) {
                $paymentPayload = [
                    'checkout_id' => $checkoutSessionId,
                    'payment_reference' => $paymentId,
                    'paid_amount_centavos' => is_numeric($amount) ? (int) $amount : null,
                ];

                if (($booking->payment_status ?? null) === 'pending') {
                    $paymentService = app(PaymentService::class);
                    $paymentService->confirmPendingBooking($booking, $paymentPayload);
                } else {
                    self::syncBookingPaymentDetails($booking, $paymentPayload);
                }

                $booking->update(['payment_reference' => $paymentId]);
                return true;
            }

            // Fallback: recover pending staycation/dayvisit checkout context.
            if (self::recoverPendingBookingFromCheckoutContext(
                $referenceNumber,
                $checkoutSessionId,
                $paymentId,
                is_numeric($amount) ? (int) $amount : null
            )) {
                return true;
            }

            // Then try PackageBooking
            $pb = self::findPackageBookingForWebhook($metadata, $referenceNumber, $checkoutSessionId);
            if ($pb) {
                return self::markPackageBookingPaid($pb, $paymentId);
            }

            // CRITICAL: Payment received but no booking found anywhere
            Log::error('PAYMENT RECEIVED BUT NO BOOKING FOUND - ATTEMPTING RESCUE CREATE', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'reference' => $referenceNumber,
                'checkout_session_id' => $checkoutSessionId,
                'metadata' => $metadata,
            ]);

            // RESCUE: Create booking from webhook data if reference exists in metadata
            if ($referenceNumber && is_numeric($amount) && $amount > 0) {
                try {
                    $rescueMetadata = self::hydrateMetadataWithBilling($metadata, $billing);
                    $rescueBooking = Booking::create(
                        self::buildRescueBookingAttributes($referenceNumber, $rescueMetadata, (int) $amount, $checkoutSessionId, $paymentId)
                    );

                    Log::info('RESCUE: Created booking from webhook data', [
                        'booking_id' => $rescueBooking->id,
                        'reference' => $referenceNumber,
                        'amount' => $amount / 100,
                    ]);

                    \App\Models\AdminSystemAlert::create([
                        'type' => 'payment',
                        'severity' => 'warning',
                        'message' => "RESCUE: Booking created from webhook. Reference: {$referenceNumber}, Amount: " . ($amount/100) . " PHP. Verify booking details!",
                        'data' => [
                            'booking_id' => $rescueBooking->id,
                            'payment_id' => $paymentId,
                            'amount' => $amount,
                            'reference' => $referenceNumber,
                        ]
                    ]);

                    return true;
                } catch (\Throwable $e) {
                    Log::error('RESCUE: Failed to create booking from webhook', [
                        'reference' => $referenceNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \App\Models\AdminSystemAlert::create([
                'type' => 'payment',
                'severity' => 'critical',
                'message' => "Payment received ({$paymentId}) but no booking found. Amount: " . ($amount/100) . " PHP. Reference: {$referenceNumber}",
                'data' => [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                    'reference' => $referenceNumber,
                    'checkout_session_id' => $checkoutSessionId,
                ]
            ]);
        }

        return false;
    }

    private static function handlePaymentFailed(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $metadata = is_array($attributes['metadata'] ?? null) ? $attributes['metadata'] : [];
        $paymentId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;
        $referenceNumber = $referenceNumber ?: ($attributes['reference_number'] ?? null);
        $referenceNumber = $referenceNumber
            ?: ($metadata['reference'] ?? null)
            ?: ($metadata['reference_id'] ?? null)
            ?: ($metadata['reference_number'] ?? null)
            ?: ($metadata['booking_reference'] ?? null);
        $checkoutSessionId = $attributes['checkout_session_id'] ?? null;

        Log::warning("PayMongo payment failed event", [
            'payment_id' => $paymentId,
            'reference' => $referenceNumber,
        ]);

        if (self::markShopOrderFailed($referenceNumber, $checkoutSessionId, $paymentId)) {
            return true;
        }

        // Try to find pending booking by transaction_id or checkout_session_id
        $booking = null;
        if ($referenceNumber) {
            $booking = Booking::where('transaction_id', $referenceNumber)
                ->where('payment_status', 'pending')
                ->first();
        }
        if (!$booking && $checkoutSessionId) {
            $booking = Booking::where('paymongo_checkout_id', $checkoutSessionId)
                ->where('payment_status', 'pending')
                ->first();
        }
        if ($booking instanceof Booking) {
            $paymentService = app(PaymentService::class);
            $paymentService->failPendingBooking($booking, $checkoutSessionId);
            return true;
        }

        $pb = self::findPackageBookingForWebhook($metadata, $referenceNumber, $checkoutSessionId);
        if ($pb) {
            $pb->payment_status = 'failed';
            $pb->payment_reference = $paymentId;
            $pb->save();
            Log::warning("PackageBooking payment failed via webhook", ['id' => $pb->id]);
            return true;
        }

        return false;
    }

    private static function handleCheckoutPaymentFailed(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $metadata = is_array($attributes['metadata'] ?? null) ? $attributes['metadata'] : [];
        $checkoutSessionId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;
        $referenceNumber = $referenceNumber ?: ($attributes['reference_number'] ?? null);

        Log::warning("PayMongo checkout payment failed", [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
        ]);

        if (self::markShopOrderFailed($referenceNumber, $checkoutSessionId)) {
            return true;
        }

        // Try to find pending booking by reference or checkout_session_id
        $booking = null;
        if ($referenceNumber) {
            $booking = Booking::where('transaction_id', $referenceNumber)
                ->where('payment_status', 'pending')
                ->first();
        }
        if (!$booking && $checkoutSessionId) {
            $booking = Booking::where('paymongo_checkout_id', $checkoutSessionId)
                ->where('payment_status', 'pending')
                ->first();
        }
        if ($booking instanceof Booking) {
            $paymentService = app(PaymentService::class);
            $paymentService->failPendingBooking($booking, $checkoutSessionId);
            return true;
        }

        // Try PackageBooking by paymongo_reference or booking_reference
        $pb = self::findPackageBookingForWebhook($metadata, $referenceNumber, $checkoutSessionId);
        if ($pb) {
            $pb->payment_status = 'failed';
            $pb->payment_reference = $checkoutSessionId;
            $pb->save();
            Log::warning("PackageBooking checkout failed via webhook", ['id' => $pb->id]);
            return true;
        }

        return false;
    }

    private static function handleCheckoutPaymentSuccess(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $checkoutSessionId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;
        $referenceNumber = $referenceNumber ?: ($attributes['reference_number'] ?? null);
        $payments = $attributes['payments'] ?? [];
        $metadata = is_array($attributes['metadata'] ?? null) ? $attributes['metadata'] : [];

        Log::info("PayMongo checkout payment success", [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
            'payment_count' => count($payments),
            'metadata' => $metadata,
        ]);

        // If there are payments, extract first
        $payment = !empty($payments) ? reset($payments) : null;
        $paymentId = $payment['id'] ?? null;
        $paymentAttributes = $payment['attributes'] ?? [];
        $amount = $paymentAttributes['amount'] ?? null;
        $billing = is_array($paymentAttributes['billing'] ?? null)
            ? $paymentAttributes['billing']
            : (is_array($attributes['billing'] ?? null) ? $attributes['billing'] : []);

        if (self::markShopOrderPaid($referenceNumber, $checkoutSessionId, $paymentId, $amount)) {
            return true;
        }

        // 1) Try Booking via reference (transaction_id or checkout_session_id)
        if ($referenceNumber || $checkoutSessionId) {
            $booking = null;
            if ($referenceNumber) {
                $booking = self::findBookingByReference($referenceNumber);
            }
            if (!$booking && $checkoutSessionId) {
                $booking = Booking::where('paymongo_checkout_id', $checkoutSessionId)
                    ->where('payment_status', 'pending')
                    ->first();
            }
            if ($booking instanceof Booking) {
                $paymentPayload = [
                    'checkout_id' => $checkoutSessionId,
                    'payment_reference' => $paymentId,
                    'paid_amount_centavos' => is_numeric($amount) ? (int) $amount : null,
                ];

                if (($booking->payment_status ?? null) === 'pending') {
                    $paymentService = app(PaymentService::class);
                    $paymentService->confirmPendingBooking($booking, $paymentPayload);
                } else {
                    self::syncBookingPaymentDetails($booking, $paymentPayload);
                }

                $booking->update(['payment_reference' => $paymentId]);
                return true;
            }
        }

        // 1.5) Fallback recovery from cached pending checkout context.
        if (self::recoverPendingBookingFromCheckoutContext(
            $referenceNumber,
            $checkoutSessionId,
            $paymentId,
            is_numeric($amount) ? (int) $amount : null
        )) {
            return true;
        }

        // 2) Try PackageBooking via metadata booking_id or booking_reference or paymongo_reference
        $pb = self::findPackageBookingForWebhook($metadata, $referenceNumber, $checkoutSessionId);

        if ($pb && $paymentId) {
            return self::markPackageBookingPaid($pb, $paymentId);
        }

        // CRITICAL: Checkout succeeded but no booking found anywhere
        Log::error('CHECKOUT PAYMENT SUCCEEDED BUT NO BOOKING FOUND - ATTEMPTING RESCUE CREATE', [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
            'amount' => $amount,
            'metadata' => $metadata,
        ]);

        // RESCUE: Create booking from webhook data if reference exists
        if ($referenceNumber && is_numeric($amount) && $amount > 0) {
            try {
                $rescueMetadata = self::hydrateMetadataWithBilling($metadata, $billing);
                $rescueBooking = Booking::create(
                    self::buildRescueBookingAttributes($referenceNumber, $rescueMetadata, (int) $amount, $checkoutSessionId, $paymentId)
                );

                Log::info('RESCUE: Created booking from checkout webhook', [
                    'booking_id' => $rescueBooking->id,
                    'reference' => $referenceNumber,
                    'amount' => $amount / 100,
                ]);

                \App\Models\AdminSystemAlert::create([
                    'type' => 'payment',
                    'severity' => 'warning',
                    'message' => "RESCUE: Booking created from checkout webhook. Reference: {$referenceNumber}, Amount: " . ($amount/100) . " PHP. Verify booking details!",
                    'data' => [
                        'booking_id' => $rescueBooking->id,
                        'checkout_session_id' => $checkoutSessionId,
                        'amount' => $amount,
                        'reference' => $referenceNumber,
                    ]
                ]);

                return true;
            } catch (\Throwable $e) {
                Log::error('RESCUE: Failed to create booking from checkout webhook', [
                    'reference' => $referenceNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \App\Models\AdminSystemAlert::create([
            'type' => 'payment',
            'severity' => 'critical',
            'message' => "Checkout payment succeeded ({$checkoutSessionId}) but no booking found. Amount: " . ($amount/100) . " PHP. Reference: {$referenceNumber}",
            'data' => [
                'checkout_session_id' => $checkoutSessionId,
                'amount' => $amount,
                'reference' => $referenceNumber,
            ]
        ]);

        return false;
    }

    public static function markPackageBookingPaid(PackageBooking $pb, ?string $paymentReference = null): bool
    {
        if (($pb->payment_status ?? null) === 'paid' && ($pb->inventory_booked ?? false)) {
            Log::info('Paymongo webhook: package booking already processed', ['id' => $pb->id]);
            return true;
        }

        try {
            DB::transaction(function () use ($pb, $paymentReference) {
                $pb->payment_status = 'paid';
                $pb->payment_reference = $paymentReference;
                $pb->status = 'confirmed';
                $pb->save();

                $inv = PackageInventory::where('package_id', $pb->package_id)
                    ->whereDate('date', $pb->booking_date)
                    ->first();

                if (!$inv || ($pb->inventory_booked ?? false)) {
                    return;
                }

                $groups = $pb->groups_booked ?? 1;
                $booked = $inv->bookGroups($groups);
                if ($booked) {
                    $pb->inventory_booked = true;
                    $pb->save();
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'info',
                        'message' => "Booked {$groups} group(s) for package inventory via payment processing (package_id={$pb->package_id}, date={$pb->booking_date})",
                        'data' => ['booking_id' => $pb->id, 'inventory_id' => $inv->id]
                    ]);
                    Log::info('Booked package inventory via payment processing', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                } else {
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'warning',
                        'message' => "Failed to book package inventory via payment processing for booking {$pb->booking_reference}",
                        'data' => ['booking_id' => $pb->id]
                    ]);
                    Log::warning('Failed to book package inventory via payment processing', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                }
            });

            try {
                Mail::to($pb->email)->queue(new PackageBookingConfirmation($pb));
            } catch (\Exception $e) {
                Log::error("Failed to queue package booking confirmation email", ['error' => $e->getMessage(), 'booking_id' => $pb->id]);
            }

            Log::info("PackageBooking marked paid via payment processing", ['id' => $pb->id, 'payment_reference' => $paymentReference]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error processing package booking payment: '.$e->getMessage(), ['booking_id' => $pb->id]);
            AdminSystemAlert::create([
                'type' => 'inventory',
                'severity' => 'critical',
                'message' => "Error processing package booking payment for booking {$pb->booking_reference}",
                'data' => ['booking_id' => $pb->id, 'error' => $e->getMessage()]
            ]);
            return false;
        }
    }

    private static function markShopOrderPaid(
        ?string $referenceNumber,
        ?string $checkoutSessionId = null,
        ?string $paymentReference = null,
        $amountCentavos = null
    ): bool {
        $query = ShopOrder::query();

        if (!empty($checkoutSessionId)) {
            $query->where('paymongo_checkout_id', $checkoutSessionId);
        } elseif (!empty($referenceNumber)) {
            $query->where('transaction_id', $referenceNumber)
                ->orWhere('order_reference', $referenceNumber);
        } else {
            return false;
        }

        $order = $query->first();
        if (!$order) {
            return false;
        }

        if (($order->payment_status ?? null) === ShopOrder::PAYMENT_STATUS_PAID) {
            return true;
        }

        $order->payment_status = ShopOrder::PAYMENT_STATUS_PAID;
        $order->order_status = ShopOrder::ORDER_STATUS_CONFIRMED;
        $order->paid_at = now();

        if (!empty($checkoutSessionId) && empty($order->paymongo_checkout_id)) {
            $order->paymongo_checkout_id = $checkoutSessionId;
        }

        $order->save();

        Log::info('ShopOrder marked paid via PayMongo webhook', [
            'shop_order_id' => $order->id,
            'order_reference' => $order->order_reference,
            'transaction_id' => $order->transaction_id,
            'checkout_session_id' => $checkoutSessionId,
            'payment_reference' => $paymentReference,
            'amount_centavos' => $amountCentavos,
        ]);

        return true;
    }

    private static function markShopOrderFailed(
        ?string $referenceNumber,
        ?string $checkoutSessionId = null,
        ?string $paymentReference = null
    ): bool {
        $query = ShopOrder::query();

        if (!empty($checkoutSessionId)) {
            $query->where('paymongo_checkout_id', $checkoutSessionId);
        } elseif (!empty($referenceNumber)) {
            $query->where('transaction_id', $referenceNumber)
                ->orWhere('order_reference', $referenceNumber);
        } else {
            return false;
        }

        $order = $query->first();
        if (!$order) {
            return false;
        }

        if (($order->payment_status ?? null) !== ShopOrder::PAYMENT_STATUS_PAID) {
            $order->payment_status = ShopOrder::PAYMENT_STATUS_FAILED;
            $order->order_status = ShopOrder::ORDER_STATUS_CANCELLED;
            $order->save();
        }

        Log::warning('ShopOrder marked failed via PayMongo webhook', [
            'shop_order_id' => $order->id,
            'order_reference' => $order->order_reference,
            'transaction_id' => $order->transaction_id,
            'checkout_session_id' => $checkoutSessionId,
            'payment_reference' => $paymentReference,
        ]);

        return true;
    }

    private static function handleUnknownEvent(string $eventType, array $data): bool
    {
        Log::info("PayMongo unknown event type", [
            'type' => $eventType,
            'data' => $data,
        ]);
        return false;
    }

    private static function findBookingByReference(string $reference): ?Booking
    {
        // Try to find by transaction_id first
        $booking = Booking::where('transaction_id', $reference)->first();
        if ($booking) {
            return $booking;
        }

        // Also try finding by paymongo_checkout_id (for webhook recovery)
        $booking = Booking::where('paymongo_checkout_id', $reference)->first();
        if ($booking) {
            return $booking;
        }

        // Add additional lookup logic if you use other reference formats
        return null;
    }

    private static function findPackageBookingForWebhook(
        array $metadata,
        ?string $referenceNumber,
        ?string $checkoutSessionId = null
    ): ?PackageBooking {
        $pb = null;

        if (!empty($metadata['booking_id'])) {
            $pb = PackageBooking::find($metadata['booking_id']);
        }
        if (!$pb && !empty($metadata['booking_reference'])) {
            $pb = PackageBooking::where('booking_reference', $metadata['booking_reference'])->first();
        }
        if (!$pb && $referenceNumber) {
            $pb = PackageBooking::where('booking_reference', $referenceNumber)
                ->orWhere('paymongo_reference', $referenceNumber)
                ->first();
        }
        if (!$pb && $checkoutSessionId) {
            $pb = PackageBooking::where('paymongo_reference', $checkoutSessionId)->first();
        }

        return $pb;
    }

    private static function recoverPendingBookingFromCheckoutContext(
        ?string $referenceNumber,
        ?string $checkoutSessionId = null,
        ?string $paymentReference = null,
        ?int $paidAmountCentavos = null
    ): bool {
        $reference = $referenceNumber;
        if (empty($reference) && !empty($checkoutSessionId)) {
            $reference = Cache::get('paymongo_checkout_reference:' . $checkoutSessionId);
        }

        if (empty($reference)) {
            Log::warning('Webhook recovery failed: no reference found', [
                'referenceNumber' => $referenceNumber,
                'checkoutSessionId' => $checkoutSessionId,
            ]);
            return false;
        }

        $existingBooking = self::findBookingByReference($reference);
        if ($existingBooking instanceof Booking) {
            $context = Cache::get('paymongo_checkout_context:' . $reference);
            $paymentData = is_array($context['payment_data'] ?? null) ? $context['payment_data'] : [];
            $paymentData['reference_id'] = $reference;
            if (!empty($checkoutSessionId)) {
                $paymentData['checkout_id'] = $checkoutSessionId;
                $paymentData['checkout_session_id'] = $checkoutSessionId;
            }
            if (!empty($paymentReference)) {
                $paymentData['payment_reference'] = $paymentReference;
            }
            if ($paidAmountCentavos !== null) {
                $paymentData['paid_amount_centavos'] = $paidAmountCentavos;
            }

            if (($existingBooking->payment_status ?? null) === 'pending') {
                /** @var \App\Services\PaymentService $paymentService */
                $paymentService = app(PaymentService::class);
                $paymentService->confirmPendingBooking($existingBooking, $paymentData);
            } else {
                self::syncBookingPaymentDetails($existingBooking, $paymentData);
            }

            Cache::forget('paymongo_checkout_context:' . $reference);
            if (!empty($checkoutSessionId)) {
                Cache::forget('paymongo_checkout_reference:' . $checkoutSessionId);
            }
            return true;
        }

        $context = Cache::get('paymongo_checkout_context:' . $reference);
        if (!is_array($context)) {
            Log::warning('Webhook recovery failed: context not in cache', [
                'reference' => $reference,
                'checkoutSessionId' => $checkoutSessionId,
                'cacheKey' => 'paymongo_checkout_context:' . $reference,
            ]);
            return false;
        }

        $type = $context['type'] ?? null;
        if (!in_array($type, ['staycation', 'dayvisit'], true)) {
            Log::warning('Webhook recovery failed: invalid booking type in cache', [
                'reference' => $reference,
                'type' => $type,
            ]);
            return false;
        }

        $bookingData = $context['booking_data'] ?? null;
        $paymentData = $context['payment_data'] ?? null;
        if (!is_array($bookingData) || !is_array($paymentData)) {
            Log::warning('Webhook recovery failed: missing booking or payment data in cache', [
                'reference' => $reference,
                'hasBookingData' => is_array($bookingData),
                'hasPaymentData' => is_array($paymentData),
            ]);
            return false;
        }

        $paymentData['reference_id'] = $reference;
        if (!empty($checkoutSessionId)) {
            $paymentData['checkout_id'] = $checkoutSessionId;
            $paymentData['checkout_session_id'] = $checkoutSessionId;
        }
        if (!empty($paymentReference)) {
            $paymentData['payment_reference'] = $paymentReference;
        }
        if ($paidAmountCentavos !== null) {
            $paymentData['paid_amount_centavos'] = $paidAmountCentavos;
        }

        try {
            /** @var \App\Services\PaymentService $paymentService */
            $paymentService = app(PaymentService::class);
            if ($type === 'staycation') {
                $paymentService->processStaycationPayment($bookingData, $paymentData);
            } elseif ($type === 'dayvisit') {
                $paymentService->processDayvisitPayment($bookingData, $paymentData);
            }

            if (self::findBookingByReference($reference) instanceof Booking) {
                Cache::forget('paymongo_checkout_context:' . $reference);
                if (!empty($checkoutSessionId)) {
                    Cache::forget('paymongo_checkout_reference:' . $checkoutSessionId);
                }
                Log::info('Recovered booking via PayMongo webhook context', [
                    'reference' => $reference,
                    'type' => $type,
                    'checkout_session_id' => $checkoutSessionId,
                ]);
                return true;
            }
        } catch (\Throwable $e) {
            Log::error('Failed to recover pending booking from webhook context', [
                'reference' => $reference,
                'type' => $type,
                'checkout_session_id' => $checkoutSessionId,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private static function buildRescueBookingAttributes(
        string $referenceNumber,
        array $metadata,
        int $amountCentavos,
        ?string $checkoutSessionId = null,
        ?string $paymentReference = null
    ): array {
        $bookingType = self::normalizeBookingType($metadata['booking_type'] ?? null);
        $checkinDate = $metadata['checkin_date'] ?? $metadata['booking_date'] ?? now()->toDateString();
        $checkoutDate = $bookingType === 'staycation'
            ? ($metadata['checkout_date'] ?? null)
            : null;

        $firstName = trim((string) ($metadata['first_name'] ?? ''));
        $lastName = trim((string) ($metadata['last_name'] ?? ''));
        $customerName = trim($firstName . ' ' . $lastName);
        if ($customerName === '') {
            $customerName = trim((string) ($metadata['customer_name'] ?? ''));
        }
        if ($customerName === '') {
            $customerName = 'Payment Received';
        }

        $bookingData = array_filter([
            'booking_type' => $bookingType,
            'staycation_category' => $metadata['staycation_category'] ?? null,
            'checkin' => $bookingType === 'staycation' ? $checkinDate : null,
            'checkout' => $bookingType === 'staycation' ? $checkoutDate : null,
            'booking_date' => $bookingType === 'dayvisit' ? $checkinDate : null,
            'customer_info' => array_filter([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $customerName,
                'email' => $metadata['email'] ?? null,
                'phone' => $metadata['phone'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),
        ], function ($value) {
            if (is_array($value)) {
                return !empty($value);
            }

            return $value !== null && $value !== '';
        });

        return [
            'transaction_id' => $referenceNumber,
            'customer_name' => $customerName,
            'customer_email' => $metadata['email'] ?? 'unknown@paymongo-webhook.local',
            'customer_phone' => $metadata['phone'] ?? '',
            'booking_type' => $bookingType,
            'checkin_date' => $checkinDate,
            'checkout_date' => $checkoutDate,
            'booking_data' => $bookingData,
            'total_amount' => ($amountCentavos / 100),
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'paymongo_checkout_id' => $checkoutSessionId,
            'payment_reference' => $paymentReference,
        ];
    }

    private static function normalizeBookingType(?string $bookingType): string
    {
        $normalized = strtolower(trim((string) $bookingType));

        if (in_array($normalized, ['glamping', 'camping', 'staycation'], true)) {
            return 'staycation';
        }

        if ($normalized === 'dayvisit') {
            return 'dayvisit';
        }

        return 'staycation';
    }

    private static function hydrateMetadataWithBilling(array $metadata, array $billing): array
    {
        $billingName = trim((string) ($billing['name'] ?? ''));
        $nameParts = preg_split('/\s+/', $billingName, 2) ?: [];

        if (empty($metadata['customer_name']) && $billingName !== '') {
            $metadata['customer_name'] = $billingName;
        }
        if (empty($metadata['first_name']) && !empty($nameParts[0])) {
            $metadata['first_name'] = $nameParts[0];
        }
        if (empty($metadata['last_name']) && !empty($nameParts[1])) {
            $metadata['last_name'] = $nameParts[1];
        }
        if (empty($metadata['email']) && !empty($billing['email'])) {
            $metadata['email'] = $billing['email'];
        }
        if (empty($metadata['phone']) && !empty($billing['phone'])) {
            $metadata['phone'] = $billing['phone'];
        }

        return $metadata;
    }

    public static function verifySignature(string $payloadString, string $signature): bool
    {
        $secret = trim((string) config('services.paymongo.webhook_secret', ''));

        if ($secret === '') {
            Log::warning("PayMongo webhook secret not configured");
            return false;
        }

        $signature = trim($signature);
        if ($signature === '') {
            return false;
        }

        // Support PayMongo canonical signature header format:
        // t=<timestamp>,te=<test_signature>,li=<live_signature>
        //
        // Signing input: "{timestamp}.{raw_json_payload}"
        // HMAC: SHA-256 using webhook secret (hex digest)
        $headerParts = self::parseSignatureHeader($signature);
        if (isset($headerParts['t'])) {
            $timestamp = (string) $headerParts['t'];
            $signedPayload = $timestamp . '.' . $payloadString;
            $expectedHex = hash_hmac('sha256', $signedPayload, $secret);

            foreach (['li', 'te'] as $key) {
                $provided = trim((string) ($headerParts[$key] ?? ''));
                if ($provided !== '' && hash_equals(strtolower($expectedHex), strtolower($provided))) {
                    return true;
                }
            }
        }

        // Backward-compatible fallback for plain signature headers.
        $rawHash = hash_hmac('sha256', $payloadString, $secret, true);
        $expectedBase64 = base64_encode($rawHash);
        $expectedHex = hash_hmac('sha256', $payloadString, $secret);

        return hash_equals($expectedBase64, $signature)
            || hash_equals(strtolower($expectedHex), strtolower($signature));
    }

    private static function parseSignatureHeader(string $signature): array
    {
        $result = [];
        $chunks = explode(',', $signature);
        foreach ($chunks as $chunk) {
            $pair = explode('=', trim($chunk), 2);
            if (count($pair) !== 2) {
                continue;
            }

            $key = trim($pair[0]);
            $value = trim($pair[1]);
            if ($key !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private static function syncBookingPaymentDetails(Booking $booking, array $paymentData): void
    {
        $updates = array_filter([
            'paymongo_checkout_id' => $paymentData['checkout_id'] ?? $paymentData['checkout_session_id'] ?? null,
            'payment_reference' => $paymentData['payment_reference'] ?? null,
        ], fn ($value) => !empty($value));

        if (!empty($updates)) {
            $booking->update($updates);
        }
    }
}
