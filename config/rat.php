<?php

return [
    'use_php' => (bool) env('RAT_USE_PHP', false),
    'paths' => [
        'app',
        'bootstrap',
        'config',
        'database',
        'resources',
        'routes',
        'tools',
    ],
    'exclude' => [
        'vendor',
        'storage',
        'bootstrap/cache',
        'node_modules',
        'public',
        '.git',
        '.idea',
        '.vscode',
        'tests',
        'tests_python',
        '.rat',
    ],
    'analysis' => [
        'routes' => true,
        'authorization' => true,
        'data_flow' => true,
        'hidden_behavior' => true,
        'impact' => true,
        'security' => true,
        'deep' => true,
    ],
    'depth' => 12,
];
