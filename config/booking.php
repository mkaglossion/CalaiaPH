<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking Capacities
    |--------------------------------------------------------------------------
    |
    | Maximum capacities for different booking types
    |
    */
    'capacities' => [
        'day_visit' => env('DAY_VISIT_CAPACITY', 10),
        'room' => env('ROOM_CAPACITY', 5),
        'special-event' => env('SPECIAL_EVENT_CAPACITY', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Inventory
    |--------------------------------------------------------------------------
    |
    | Default inventory levels for different booking types by date
    |
    */
    'default_inventory' => [
        'day-visit' => [
            '2025-11-20' => env('DAY_VISIT_DEFAULT_2025_11_20', 8),
            '2025-11-21' => env('DAY_VISIT_DEFAULT_2025_11_21', 5),
            '2025-11-22' => env('DAY_VISIT_DEFAULT_2025_11_22', 10),
        ],
        'room' => [
            '2025-11-20' => env('ROOM_DEFAULT_2025_11_20', 3),
            '2025-11-21' => env('ROOM_DEFAULT_2025_11_21', 2),
            '2025-11-22' => env('ROOM_DEFAULT_2025_11_22', 5),
        ],
        'special-event' => [
            '2025-11-20' => env('SPECIAL_EVENT_DEFAULT_2025_11_20', 2),
            '2025-11-21' => env('OVERNIGHT_DEFAULT_2025_11_21', 1),
            '2025-11-22' => env('OVERNIGHT_DEFAULT_2025_11_22', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Staycation Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing configuration for staycation accommodations
    |
    */
    'staycation_pricing' => [
        'standard' => [
            'weekday' => env('STANDARD_WEEKDAY_PRICE', 4800),
            'weekend' => env('STANDARD_WEEKEND_PRICE', 6000),
            'extra_guest' => env('STANDARD_EXTRA_GUEST_PRICE', 400),
        ],
        'premium' => [
            'weekday' => env('PREMIUM_WEEKDAY_PRICE', 5760),
            'weekend' => env('PREMIUM_WEEKEND_PRICE', 7200),
            'extra_guest' => env('PREMIUM_EXTRA_GUEST_PRICE', 400),
        ],
        'bell_tent' => env('BELL_TENT_PRICE', 3000),
        'dome_tent' => env('DOME_TENT_PRICE', 3000),
        'byot' => env('BYOT_PRICE', 300),
        'car_camping' => env('CAR_CAMPING_PRICE', 300),
        'pet_fee' => env('PET_FEE', 2000),
        'breakfast_fee' => env('BREAKFAST_FEE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Pricing
    |--------------------------------------------------------------------------
    |
    | Default pricing for activities
    |
    */
    'activity_pricing' => [
        'day_visit_base' => env('DAY_VISIT_BASE_PRICE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Common validation rules used across booking forms
    |
    */
    'validation' => [
        'booking' => [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'required|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:1000',
            'type' => 'required|string|in:day-visit,room,special-event',
            'package' => 'required|string',
            'price' => 'required|numeric',
        ],
        'payment' => [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:gcash,paymaya,bank',
            'terms' => 'accepted',
            'special_requests' => 'nullable|string|max:1000',
        ],
        'activities' => [
            'activities' => 'required|array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined',
        ],
    ],
];