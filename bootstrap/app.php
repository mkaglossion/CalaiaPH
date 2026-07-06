<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$environment = $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?? null;

// Never let a cached config file bleed into the test runner.
// A stale cache can point tests at the local MariaDB database and make
// php artisan test wipe real data through RefreshDatabase migrations.
if ($environment === 'testing') {
    $testingOverrides = [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'MAIL_MAILER' => 'array',
    ];

    foreach ($testingOverrides as $key => $value) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    $cachedConfig = __DIR__ . '/cache/config.php';

    if (is_file($cachedConfig)) {
        @unlink($cachedConfig);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\CheckForMaintenanceMode::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'api.throttle' => \App\Http\Middleware\ApiRateLimiter::class,
            'api.security' => \App\Http\Middleware\ApiSecurity::class,
            'xss' => \App\Http\Middleware\XssProtection::class,
            'ensure.two.factor.verified' => \App\Http\Middleware\EnsureTwoFactorIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
