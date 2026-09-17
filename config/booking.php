<?php

return [
    'payment_attempt_expiry_minutes' => 1440,

    // Successful webhook logs remain available for audits before safe cleanup.
    'paymongo_webhook_retention_days' => max(1, (int) env('PAYMONGO_WEBHOOK_RETENTION_DAYS', 180)),
    'paymongo_invalid_webhook_retention_days' => max(1, (int) env('PAYMONGO_INVALID_WEBHOOK_RETENTION_DAYS', 30)),

    'checkin_reminder' => [
        'enabled' => filter_var(env('BOOKING_CHECKIN_REMINDER_ENABLED', true), FILTER_VALIDATE_BOOL),
        'days_before' => (int) env('BOOKING_CHECKIN_REMINDER_DAYS_BEFORE', 1),
        'send_time' => env('BOOKING_CHECKIN_REMINDER_TIME', '09:00'),
    ],

    'home_availability_widget' => [
        // Temporarily hidden; retain the underlying logic for easy restoration.
        'show_availability_status' => false,
        'show_guest_selector' => false,
    ],

    'staycation_availability' => [
        // Temporarily hidden because room slots no longer have a fixed maximum.
        'show_room_slot_counter' => false,
        'show_empty_room_target' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the booking system including cancellation
    | policies, payment settings, and other booking-related options.
    |
    */

    'cancellation_policy' => [
        /*
        |--------------------------------------------------------------------------
        | Cancellation Policy Settings - Tiered Refund Scale
        |--------------------------------------------------------------------------
        |
        | Define the cancellation policy with tiered refunds based on days before check-in:
        | - 30+ days: 100% refund
        | - 15-29 days: 75% refund
        | - 7-14 days: 50% refund
        | - 3-6 days: 25% refund
        | - < 3 days: 0% refund
        |
        | For the multi-tier system, use the 'tiered_refunds' option instead.
        | If 'use_tiered_refunds' => true, the tiered percentages will be used.
        |
        */
        'use_tiered_refunds' => true,
        'tiered_refunds' => [
            ['days' => 30, 'percentage' => 100],   // 30+ days: 100%
            ['days' => 15, 'percentage' => 75],    // 15-29 days: 75%
            ['days' => 7, 'percentage' => 50],     // 7-14 days: 50%
            ['days' => 3, 'percentage' => 25],     // 3-6 days: 25%
            ['days' => 0, 'percentage' => 0],      // < 3 days: 0%
        ],

        // Peak season stricter refund tiers (Christmas, Holy Week, Summer)
        'peak_season_tiered_refunds' => [
            ['days' => 7, 'percentage' => 100],    // 7+ days: 100% (stricter)
            ['days' => 3, 'percentage' => 50],     // 3-6 days: 50%
            ['days' => 0, 'percentage' => 0],      // < 3 days: 0%
        ],
        
        // Legacy settings (used if use_tiered_refunds => false)
        'free_cancellation_days' => 3,        // Free cancellation if ≥ 3 days before check-in
        'partial_refund_days' => 1,           // 50% refund if 1-3 days before check-in
        'partial_refund_percentage' => 0.5,   // 50% refund
        'no_refund_hours' => 24,              // No refund if < 24 hours before check-in
        'allow_immediate_rebooking' => true,  // Can rebook immediately after cancellation
    ],

    /*
    |--------------------------------------------------------------------------
    | Peak Season Dates
    |--------------------------------------------------------------------------
    |
    | Define Philippine peak seasons for stricter cancellation policies.
    | Formats: 'MM-DD'
    |
    */
    'peak_seasons' => [
        ['start' => '12-15', 'end' => '01-05'],  // Christmas (Dec 15 - Jan 5)
        ['start' => '03-20', 'end' => '04-10'],  // Holy Week (Mar 20 - Apr 10)
        ['start' => '04-01', 'end' => '05-31'],  // Summer Break (Apr 1 - May 31)
    ],

    /*
    |--------------------------------------------------------------------------
    | Rebooking Settings
    |--------------------------------------------------------------------------
    |
    | Settings for rebooking functionality after cancellation.
    |
    */
    'rebooking' => [
        'credit_validity_months' => 6,       // Credits valid for 6 months
        'apply_discount_to_rebooking' => true, // Apply refund as discount to rebooking
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Types
    |--------------------------------------------------------------------------
    |
    | Define different booking types and their configurations.
    |
    */
    'types' => [
        'staycation' => [
            'name' => 'Staycation',
            'requires_checkin_checkout' => true,
            'supports_cancellation' => true,
        ],
        'dayvisit' => [
            'name' => 'Day Visit',
            'requires_checkin_checkout' => false,
            'supports_cancellation' => true,
        ],
        'regular' => [
            'name' => 'Regular Booking',
            'requires_checkin_checkout' => false,
            'supports_cancellation' => true,
        ],
    ],
];
