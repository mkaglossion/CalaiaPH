<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class PaymongoWebhookService
{
    /**
     * Handle PayMongo webhook events
     */
    public static function handleWebhook(array $payload)
    {
        $eventType = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];

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
            default => self::handleUnknownEvent($eventType, $data),
        };
    }

    /**
     * Handle payment.paid event
     */
    private static function handlePaymentPaid(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $paymentId = $data['id'] ?? null;
        $status = $attributes['status'] ?? null;
        $amount = $attributes['amount'] ?? 0;
        $referenceNumber = $attributes['reference'] ?? null;

        Log::info("PayMongo payment paid event", [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'reference' => $referenceNumber,
        ]);

        if ($status === 'paid' && $referenceNumber) {
            // Extract booking reference from the reference number
            // Reference format: CALAIA-XXXX or custom reference
            $booking = self::findBookingByReference($referenceNumber);

            if ($booking) {
                BookingStatusService::updatePaymentStatus(
                    $booking,
                    'paid',
                    $amount / 100, // PayMongo sends in centavos
                    'paymongo',
                    $paymentId,
                    'webhook'
                );
                return true;
            } else {
                Log::warning("Could not find booking for payment reference", [
                    'reference' => $referenceNumber,
                    'payment_id' => $paymentId,
                ]);
            }
        }

        return false;
    }

    /**
     * Handle payment.failed event
     */
    private static function handlePaymentFailed(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $paymentId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;

        Log::warning("PayMongo payment failed event", [
            'payment_id' => $paymentId,
            'reference' => $referenceNumber,
        ]);

        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);

            if ($booking) {
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

        return false;
    }

    /**
     * Handle checkout_session.payment_failed event
     */
    private static function handleCheckoutPaymentFailed(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $checkoutSessionId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;

        Log::warning("PayMongo checkout payment failed", [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
        ]);

        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);

            if ($booking) {
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

        return false;
    }

    /**
     * Handle checkout_session.payment_success event
     */
    private static function handleCheckoutPaymentSuccess(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $checkoutSessionId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;
        $payments = $attributes['payments'] ?? [];

        Log::info("PayMongo checkout payment success", [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
            'payment_count' => count($payments),
        ]);

        if ($referenceNumber) {
            $booking = self::findBookingByReference($referenceNumber);

            if ($booking && !empty($payments)) {
                // Get the first payment (usually the paid one)
                $payment = reset($payments);
                $paymentId = $payment['id'] ?? null;
                $paymentAttributes = $payment['attributes'] ?? [];
                $amount = $paymentAttributes['amount'] ?? $booking->total_amount * 100;

                BookingStatusService::updatePaymentStatus(
                    $booking,
                    'paid',
                    $amount / 100, // Convert from centavos
                    'paymongo',
                    $paymentId,
                    'webhook'
                );
                return true;
            }
        }

        return false;
    }

    /**
     * Handle unknown events
     */
    private static function handleUnknownEvent(string $eventType, array $data): bool
    {
        Log::info("PayMongo unknown event type", [
            'type' => $eventType,
            'data' => $data,
        ]);
        return false;
    }

    /**
     * Find booking by PayMongo reference number
     */
    private static function findBookingByReference(string $reference): ?Booking
    {
        // Try to find by transaction_id first
        $booking = Booking::where('transaction_id', $reference)->first();

        if (!$booking) {
            // Try to find by customer data or other references
            // Extract booking ID if reference format is CALAIA-BOOKINGID-XXXX
            if (strpos($reference, 'CALAIA-') === 0) {
                Log::info("Looking up booking by reference", ['reference' => $reference]);
            }
        }

        return $booking;
    }

    /**
     * Verify webhook signature (for security)
     */
    public static function verifySignature(string $payloadString, string $signature): bool
    {
        $secret = config('services.paymongo.webhook_secret');

        if (!$secret) {
            Log::warning("PayMongo webhook secret not configured");
            return false;
        }

        $hash = hash_hmac('sha256', $payloadString, $secret, true);
        $calculatedSignature = base64_encode($hash);

        return hash_equals($calculatedSignature, $signature);
    }
}
