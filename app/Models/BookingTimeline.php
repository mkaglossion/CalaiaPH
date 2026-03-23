<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'event_type',
        'old_value',
        'new_value',
        'field_name',
        'description',
        'triggered_by',
        'admin_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: BookingTimeline belongs to Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship: BookingTimeline belongs to Admin (User)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Static method to log a timeline event
     */
    public static function log(
        $bookingId,
        $eventType,
        $description = null,
        $triggeredBy = 'system',
        $adminId = null,
        $oldValue = null,
        $newValue = null,
        $fieldName = null,
        $metadata = null
    ) {
        return self::create([
            'booking_id' => $bookingId,
            'event_type' => $eventType,
            'description' => $description,
            'triggered_by' => $triggeredBy,
            'admin_id' => $adminId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'field_name' => $fieldName,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get human-friendly event label
     */
    public function getEventLabelAttribute(): string
    {
        $labels = [
            'status_changed' => 'Status Changed',
            'payment_received' => 'Payment Received',
            'payment_failed' => 'Payment Failed',
            'verification_completed' => 'Verified',
            'booking_created' => 'Booking Created',
            'booking_confirmed' => 'Booking Confirmed',
            'booking_cancelled' => 'Booking Cancelled',
            'booking_completed' => 'Booking Completed',
            'checkin_completed' => 'Checked In',
            'checkout_completed' => 'Checked Out',
            'notes_added' => 'Notes Added',
            'reminder_sent' => 'Reminder Sent',
        ];

        return $labels[$this->event_type] ?? ucwords(str_replace('_', ' ', $this->event_type));
    }

    /**
     * Get icon for event type
     */
    public function getEventIconAttribute(): string
    {
        $icons = [
            'status_changed' => 'sync',
            'payment_received' => 'credit-card',
            'payment_failed' => 'alert-circle',
            'verification_completed' => 'check-circle',
            'booking_created' => 'plus-circle',
            'booking_confirmed' => 'check',
            'booking_cancelled' => 'x-circle',
            'booking_completed' => 'flag',
            'checkin_completed' => 'log-in',
            'checkout_completed' => 'log-out',
            'notes_added' => 'message-square',
            'reminder_sent' => 'bell',
        ];

        return $icons[$this->event_type] ?? 'info';
    }

    /**
     * Get color for event type (Tailwind classes)
     */
    public function getEventColorAttribute(): string
    {
        $colors = [
            'status_changed' => 'blue',
            'payment_received' => 'green',
            'payment_failed' => 'red',
            'verification_completed' => 'green',
            'booking_created' => 'blue',
            'booking_confirmed' => 'green',
            'booking_cancelled' => 'red',
            'booking_completed' => 'purple',
            'checkin_completed' => 'emerald',
            'checkout_completed' => 'amber',
            'notes_added' => 'indigo',
            'reminder_sent' => 'cyan',
        ];

        return $colors[$this->event_type] ?? 'gray';
    }
}
