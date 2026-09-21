<?php

return [
    'api_key' => env('OPENWEATHER_API_KEY'),
    'base_url' => env('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5'),

    'city' => env('WEATHER_CITY', 'Purok 3, Brgy. Bisaya, Calaca'),
    'country' => env('WEATHER_COUNTRY', 'PH'),
    'latitude' => (float) env('WEATHER_LATITUDE', 13.9298),
    'longitude' => (float) env('WEATHER_LONGITUDE', 120.8133),

    'cache_minutes' => (int) env('WEATHER_CACHE_MINUTES', 30),
    'stale_minutes' => (int) env('WEATHER_STALE_MINUTES', 120),
    'connect_timeout_seconds' => (int) env('WEATHER_CONNECT_TIMEOUT_SECONDS', 2),
    'timeout_seconds' => (int) env('WEATHER_TIMEOUT_SECONDS', 4),
];
