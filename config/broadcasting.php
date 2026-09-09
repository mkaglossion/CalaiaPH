<?php

$pusherKey = env('PUSHER_APP_KEY');
$pusherSecret = env('PUSHER_APP_SECRET');
$pusherAppId = env('PUSHER_APP_ID');
$defaultConnection = env('BROADCAST_CONNECTION', 'log');
$hasPusherCredentials = filled($pusherKey) && filled($pusherSecret) && filled($pusherAppId);

if ($defaultConnection === 'pusher' && ! $hasPusherCredentials) {
    $defaultConnection = 'log';
}

return [
    'default' => $defaultConnection,

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => $pusherKey,
            'secret' => $pusherSecret,
            'app_id' => $pusherAppId,
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'encrypted' => true,
                'host' => 'api-' . env('PUSHER_APP_CLUSTER') . '.pusher.com',
                'port' => 443,
                'scheme' => 'https',
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
