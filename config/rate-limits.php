<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiters for your application.
    | You can define multiple rate limiters with different configurations.
    | Decay is in seconds for faster recovery.
    |
    */

    'api' => [
        'max_attempts' => env('API_RATE_LIMIT', 60),
        'decay_seconds' => env('API_RATE_LIMIT_DECAY', 60), // 60 seconds = 1 minute
    ],

    'api_strict' => [
        'max_attempts' => env('API_STRICT_RATE_LIMIT', 10),
        'decay_seconds' => env('API_STRICT_RATE_LIMIT_DECAY', 30), // 30 seconds
    ],

    'upload' => [
        'max_attempts' => env('UPLOAD_RATE_LIMIT', 5),
        'decay_seconds' => env('UPLOAD_RATE_LIMIT_DECAY', 60), // 60 seconds
    ],

    'booking' => [
        'max_attempts' => env('BOOKING_RATE_LIMIT', 30),
        'decay_seconds' => env('BOOKING_RATE_LIMIT_DECAY', 60), // 60 seconds
    ],
];
