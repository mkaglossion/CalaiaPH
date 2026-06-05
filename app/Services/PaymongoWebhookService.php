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

            // Try existing Booking flow first
            if ($referenceNumber) {
                $booking = self::findBookingByReference($referenceNumber);
                if ($booking instanceof Booking) {
                    BookingStatusService::updatePaymentStatus(
                        $booking,
                        'paid',
                        $amount / 100,
                        'paymongo',
                        $paymentId,
                        'webhook'
                    );
                    return true;
                }
            }

            // Fallback: recover pending staycation/dayvisit checkout context.
            if (self::recoverPendingBookingFromCheckoutContext(
                $referenceNumber,
                null,
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

        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);
            if ($booking instanceof Booking) {
                BookingStatusService::updatePaymentStatus(
                    $booking,
                    'failed',
                    $booking->total_amount,
                    'paymongo',
                    $paymentId,
                    'webhook'
                );
                return true;
            }
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

        // Try Booking first
        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);
            if ($booking instanceof Booking) {
                BookingStatusService::updatePaymentStatus(
                    $booking,
                    'failed',
                    $booking->total_amount,
                    'paymongo',
                    $checkoutSessionId,
                    'webhook'
                );
                return true;
            }
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

        if (self::markShopOrderPaid($referenceNumber, $checkoutSessionId, $paymentId, $amount)) {
            return true;
        }

        // 1) Try Booking via reference
        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);
            if ($booking instanceof Booking && $paymentId) {
                BookingStatusService::updatePaymentStatus(
                    $booking,
                    'paid',
                    ($amount ?? $booking->total_amount * 100) / 100,
                    'paymongo',
                    $paymentId,
                    'webhook'
                );
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
            return false;
        }

        if (self::findBookingByReference($reference) instanceof Booking) {
            Cache::forget('paymongo_checkout_context:' . $reference);
            if (!empty($checkoutSessionId)) {
                Cache::forget('paymongo_checkout_reference:' . $checkoutSessionId);
            }
            return true;
        }

        $context = Cache::get('paymongo_checkout_context:' . $reference);
        if (!is_array($context)) {
            return false;
        }

        $type = $context['type'] ?? null;
        if (!in_array($type, ['staycation', 'dayvisit'], true)) {
            return false;
        }

        $bookingData = $context['booking_data'] ?? null;
        $paymentData = $context['payment_data'] ?? null;
        if (!is_array($bookingData) || !is_array($paymentData)) {
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
}
