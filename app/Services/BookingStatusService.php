<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingTimeline;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class BookingStatusService
{
    /**
     * Valid status transitions
     */
    private const VALID_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Update booking status with validation and logging
     */
    public static function updateStatus(
        Booking $booking,
        string $newStatus,
        string $triggeredBy = 'system',
        ?int $adminId = null,
        ?array $metadata = null
    ): bool {
        $oldStatus = $booking->status;

        // Validate transition
        if (!self::isValidTransition($oldStatus, $newStatus)) {
            Log::warning("Invalid status transition attempted", [
                'booking_id' => $booking->id,
                'from' => $oldStatus,
                'to' => $newStatus,
                'triggered_by' => $triggeredBy,
            ]);
            return false;
        }

        // Update status
        $booking->status = $newStatus;
        $booking->save();

        // Log the change
        BookingTimeline::log(
            $booking->id,
            'status_changed',
            "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus),
            $triggeredBy,
            $adminId,
            $oldStatus,
            $newStatus,
            'status',
            $metadata
        );

        // Send email notification
        self::sendStatusChangeEmail($booking, $newStatus);

        // Execute actions for specific status changes
        self::executeStatusActions($booking, $newStatus);

        Log::info("Booking status updated", [
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'triggered_by' => $triggeredBy,
        ]);

        return true;
    }

    /**
     * Check if status transition is valid
     */
    public static function isValidTransition(string $oldStatus, string $newStatus): bool
    {
        if ($oldStatus === $newStatus) {
            return false; // No change
        }

        $validTransitions = self::VALID_TRANSITIONS[$oldStatus] ?? [];
        return in_array($newStatus, $validTransitions, true);
    }

    /**
     * Update payment status with logging
     */
    public static function updatePaymentStatus(
        Booking $booking,
        string $newPaymentStatus,
        float $amount,
        string $method,
        ?string $reference = null,
        string $triggeredBy = 'system',
        ?int $adminId = null
    ): bool {
        $oldPaymentStatus = $booking->payment_status;

        // Update payment status
        $booking->payment_status = $newPaymentStatus;
        $booking->payment_method = $method;
        $booking->save();

        // Log the payment event
        $eventType = match($newPaymentStatus) {
            'paid' => 'payment_received',
            'failed' => 'payment_failed',
            default => 'payment_status_changed',
        };

        $metadata = [
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
        ];

        try {
            BookingTimeline::log(
                $booking->id,
                $eventType,
                "Payment of ₱" . number_format($amount, 2) . " via " . ucfirst($method) . ($reference ? " (Ref: {$reference})" : ""),
                $triggeredBy,
                $adminId,
                $oldPaymentStatus,
                $newPaymentStatus,
                'payment_status',
                $metadata
            );
        } catch (\Throwable $e) {
            Log::error("Failed to write booking payment timeline entry", [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Send payment confirmation email
        if ($newPaymentStatus === 'paid') {
            self::sendPaymentConfirmationEmail($booking, $amount, $method, $reference);
        }

        Log::info("Booking payment status updated", [
            'booking_id' => $booking->id,
            'old_payment_status' => $oldPaymentStatus,
            'new_payment_status' => $newPaymentStatus,
            'amount' => $amount,
            'method' => $method,
            'triggered_by' => $triggeredBy,
        ]);

        return true;
    }

    /**
     * Execute actions based on status change
     */
    private static function executeStatusActions(Booking $booking, string $newStatus): void
    {
        match($newStatus) {
            'confirmed' => self::onBookingConfirmed($booking),
            'completed' => self::onBookingCompleted($booking),
            'cancelled' => self::onBookingCancelled($booking),
            default => null,
        };
    }

    /**
     * Actions when booking is confirmed
     */
    private static function onBookingConfirmed(Booking $booking): void
    {
        // Could send confirmation email, update inventory, etc.
        Log::info("Booking confirmed: {$booking->id}");
    }

    /**
     * Actions when booking is completed
     */
    private static function onBookingCompleted(Booking $booking): void
    {
        // Clear any pending holds/reservations
        // Send completion confirmation
        Log::info("Booking completed: {$booking->id}");
    }

    /**
     * Actions when booking is cancelled
     */
    private static function onBookingCancelled(Booking $booking): void
    {
        // Restore inventory
        $booking->restoreInventory();
        Log::info("Booking cancelled, inventory restored: {$booking->id}");
    }

    /**
     * Send status change email notification
     */
    private static function sendStatusChangeEmail(Booking $booking, string $newStatus): void
    {
        try {
            $subject = match($newStatus) {
                'confirmed' => 'Your Booking is Confirmed!',
                'completed' => 'Your Booking is Complete',
                'cancelled' => 'Your Booking has been Cancelled',
                default => 'Booking Status Update',
            };

            Mail::send('emails.booking-status-update', [
                'booking' => $booking,
                'newStatus' => $newStatus,
                'subject' => $subject,
            ], function ($m) use ($booking, $subject) {
                $m->to($booking->customer_email)
                  ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error("Failed to send status change email", [
                'booking_id' => $booking->id,
                'email' => $booking->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payment confirmation email
     */
    private static function sendPaymentConfirmationEmail(
        Booking $booking,
        float $amount,
        string $method,
        ?string $reference
    ): void {
        try {
            Mail::send('emails.payment-confirmation', [
                'booking' => $booking,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
            ], function ($m) use ($booking) {
                $m->to($booking->customer_email)
                  ->subject('Payment Received - Booking Confirmation');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send payment confirmation email", [
                'booking_id' => $booking->id,
                'email' => $booking->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all possible next statuses for a booking
     */
    public static function getPossibleNextStatuses(Booking $booking): array
    {
        return self::VALID_TRANSITIONS[$booking->status] ?? [];
    }

    /**
     * Get timeline history for a booking
     */
    public static function getTimeline(Booking $booking)
    {
        return $booking->timelines()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

