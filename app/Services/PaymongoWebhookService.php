<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\PackageInventory;
use App\Models\AdminSystemAlert;
use Illuminate\Support\Facades\DB;
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

        if ($status === 'paid') {
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

            // Then try PackageBooking
            if ($referenceNumber) {
                $pb = PackageBooking::where('booking_reference', $referenceNumber)
                    ->orWhere('paymongo_reference', $referenceNumber)
                    ->first();
                if ($pb) {
                    // Idempotency: if already processed, skip
                    if (($pb->payment_status ?? null) === 'paid' && ($pb->inventory_booked ?? false)) {
                        Log::info('Paymongo webhook: package booking already processed', ['id' => $pb->id]);
                        return true;
                    }

                    try {
                        DB::transaction(function () use ($pb, $paymentId) {
                            $pb->payment_status = 'paid';
                            $pb->payment_reference = $paymentId;
                            $pb->status = 'confirmed';
                            $pb->save();

                            $inv = PackageInventory::where('package_id', $pb->package_id)
                                ->whereDate('date', $pb->booking_date)
                                ->first();

                            if ($inv) {
                                $groups = $pb->groups_booked ?? 1;
                                if (!($pb->inventory_booked ?? false)) {
                                    $booked = $inv->bookGroups($groups);
                                    if ($booked) {
                                        $pb->inventory_booked = true;
                                        $pb->save();
                                        AdminSystemAlert::create([
                                            'type' => 'inventory',
                                            'severity' => 'info',
                                            'message' => "Booked {$groups} group(s) for package inventory via webhook (package_id={$pb->package_id}, date={$pb->booking_date})",
                                            'data' => ['booking_id' => $pb->id, 'inventory_id' => $inv->id]
                                        ]);
                                        Log::info('Booked package inventory via webhook', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                                    } else {
                                        AdminSystemAlert::create([
                                            'type' => 'inventory',
                                            'severity' => 'warning',
                                            'message' => "Failed to book package inventory via webhook for booking {$pb->booking_reference}",
                                            'data' => ['booking_id' => $pb->id]
                                        ]);
                                        Log::warning('Failed to book package inventory via webhook', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                                    }
                                }
                            }
                        });

                        try {
                            Mail::to($pb->email)->queue(new PackageBookingConfirmation($pb));
                        } catch (\Exception $e) {
                            Log::error("Failed to queue package booking confirmation email", ['error' => $e->getMessage(), 'booking_id' => $pb->id]);
                        }

                        Log::info("PackageBooking marked paid via payment.paid webhook", ['id' => $pb->id, 'payment_id' => $paymentId]);
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error processing package booking payment via webhook: '.$e->getMessage(), ['booking_id' => $pb->id]);
                        AdminSystemAlert::create([
                            'type' => 'inventory',
                            'severity' => 'critical',
                            'message' => "Error processing package booking via webhook for booking {$pb->booking_reference}",
                            'data' => ['booking_id' => $pb->id, 'error' => $e->getMessage()]
                        ]);
                        return false;
                    }
                }
            }
        }

        return false;
    }

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

            $pb = PackageBooking::where('booking_reference', $referenceNumber)
                ->orWhere('paymongo_reference', $referenceNumber)
                ->first();
            if ($pb) {
                $pb->payment_status = 'failed';
                $pb->payment_reference = $paymentId;
                $pb->save();
                Log::warning("PackageBooking payment failed via webhook", ['id' => $pb->id]);
                return true;
            }
        }

        return false;
    }

    private static function handleCheckoutPaymentFailed(array $data): bool
    {
        $attributes = $data['attributes'] ?? [];
        $checkoutSessionId = $data['id'] ?? null;
        $referenceNumber = $attributes['reference'] ?? null;

        Log::warning("PayMongo checkout payment failed", [
            'checkout_session_id' => $checkoutSessionId,
            'reference' => $referenceNumber,
        ]);

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
        $pb = null;
        if ($checkoutSessionId) {
            $pb = PackageBooking::where('paymongo_reference', $checkoutSessionId)->first();
        }
        if (!$pb && $referenceNumber) {
            $pb = PackageBooking::where('booking_reference', $referenceNumber)->first();
        }
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
        $payments = $attributes['payments'] ?? [];
        $metadata = $attributes['metadata'] ?? [];

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

        // 2) Try PackageBooking via metadata booking_id or booking_reference or paymongo_reference
        $pb = null;
        if (!empty($metadata['booking_id'])) {
            $pb = PackageBooking::find($metadata['booking_id']);
        }
        if (!$pb && !empty($metadata['booking_reference'])) {
            $pb = PackageBooking::where('booking_reference', $metadata['booking_reference'])->first();
        }
        if (!$pb && $referenceNumber) {
            $pb = PackageBooking::where('booking_reference', $referenceNumber)->first();
        }
        if (!$pb && $checkoutSessionId) {
            $pb = PackageBooking::where('paymongo_reference', $checkoutSessionId)->first();
        }

        if ($pb && $paymentId) {
            // Idempotency: if already processed, skip
            if (($pb->payment_status ?? null) === 'paid' && ($pb->inventory_booked ?? false)) {
                Log::info('Paymongo webhook: package booking already processed (checkout_success)', ['id' => $pb->id]);
                return true;
            }

            try {
                DB::transaction(function () use ($pb, $paymentId) {
                    $pb->payment_status = 'paid';
                    $pb->payment_reference = $paymentId;
                    $pb->status = 'confirmed';
                    $pb->save();

                    $inv = PackageInventory::where('package_id', $pb->package_id)
                        ->whereDate('date', $pb->booking_date)
                        ->first();

                    if ($inv) {
                        $groups = $pb->groups_booked ?? 1;
                        if (!($pb->inventory_booked ?? false)) {
                            $booked = $inv->bookGroups($groups);
                            if ($booked) {
                                $pb->inventory_booked = true;
                                $pb->save();
                                AdminSystemAlert::create([
                                    'type' => 'inventory',
                                    'severity' => 'info',
                                    'message' => "Booked {$groups} group(s) for package inventory via webhook (package_id={$pb->package_id}, date={$pb->booking_date})",
                                    'data' => ['booking_id' => $pb->id, 'inventory_id' => $inv->id]
                                ]);
                                Log::info('Booked package inventory via webhook', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                            } else {
                                AdminSystemAlert::create([
                                    'type' => 'inventory',
                                    'severity' => 'warning',
                                    'message' => "Failed to book package inventory via webhook for booking {$pb->booking_reference}",
                                    'data' => ['booking_id' => $pb->id]
                                ]);
                                Log::warning('Failed to book package inventory via webhook', ['booking_id' => $pb->id, 'inventory_id' => $inv->id]);
                            }
                        }
                    }
                });

                try {
                    Mail::to($pb->email)->queue(new PackageBookingConfirmation($pb));
                } catch (\Exception $e) {
                    Log::error("Failed to queue package booking confirmation email", ['error' => $e->getMessage(), 'booking_id' => $pb->id]);
                }

                Log::info("PackageBooking marked paid via checkout_session.payment_success", ['id' => $pb->id, 'payment_id' => $paymentId]);
                return true;
            } catch (\Exception $e) {
                Log::error('Error processing package booking payment via webhook: '.$e->getMessage(), ['booking_id' => $pb->id]);
                AdminSystemAlert::create([
                    'type' => 'inventory',
                    'severity' => 'critical',
                    'message' => "Error processing package booking via webhook for booking {$pb->booking_reference}",
                    'data' => ['booking_id' => $pb->id, 'error' => $e->getMessage()]
                ]);
                return false;
            }
        }

        return false;
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
