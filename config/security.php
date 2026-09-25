<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Security Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the security settings for your API endpoints.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Block Suspicious User Agents
    |--------------------------------------------------------------------------
    |
    | This option controls whether to block requests from suspicious user agents.
    |
    */

    'block_suspicious_user_agents' => env('BLOCK_SUSPICIOUS_USER_AGENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Suspicious User Agent Patterns
    |--------------------------------------------------------------------------
    |
    | List of user agent patterns that should be blocked.
    |
    */

    'suspicious_user_agent_patterns' => [
        'sqlmap',
        'nikto',
        'w3af',
        'nmap',
        'nessus',
        'metasploit',
        'curl',
        'wget',
        'python-requests',
        'libwww-perl',
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Protection
    |--------------------------------------------------------------------------
    |
    | This option controls whether to check for SQL injection patterns in requests.
    |
    */

    'sql_injection_protection' => env('SQL_INJECTION_PROTECTION', true),

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Patterns
    |--------------------------------------------------------------------------
    |
    | List of SQL injection patterns that should be blocked.
    |
    */

    'sql_injection_patterns' => [
        '/\b(union|select|insert|update|delete|drop|alter|create|truncate)\b/i',
        '/\b(or|and)\s*1\s*=\s*1\b/i',
        '/\b--|#|;\s*--/i',
        '/\bexec\(|sp_executesql\b/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Require JSON Content Type
    |--------------------------------------------------------------------------
    |
    | This option controls whether POST/PUT/PATCH requests must have JSON content type.
    |
    */

    'require_json_content_type' => env('REQUIRE_JSON_CONTENT_TYPE', true),

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | List of security headers to add to API responses.
    |
    */

    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    'web_security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        'Content-Security-Policy-Report-Only' => implode(' ', [
            "default-src 'self';",
            "base-uri 'self';",
            "object-src 'none';",
            "frame-ancestors 'self';",
            "form-action 'self';",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://unpkg.com https://use.fontawesome.com;",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com https://use.fontawesome.com;",
            "font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com https://use.fontawesome.com;",
            "img-src 'self' data: blob: https://res.cloudinary.com https://maps.google.com https://www.google.com https://www.facebook.com;",
            "media-src 'self' blob: https://res.cloudinary.com;",
            "connect-src 'self' https://api.openweathermap.org https://api.cloudinary.com https://api.paymongo.com https://*.pusher.com wss://*.pusher.com;",
            "frame-src 'self' https://maps.google.com https://www.google.com https://www.facebook.com;",
            "worker-src 'self' blob:;",
            "manifest-src 'self';",
        ]),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Hosts
    |--------------------------------------------------------------------------
    |
    | Exact production hosts accepted by Laravel. TrustHosts automatically
    | skips local requests and unit tests, so development remains unaffected.
    |
    */

    'trusted_hosts' => array_values(array_map(
        static fn (string $host): string => '^'.preg_quote($host, '/').'$',
        array_filter(array_map('trim', explode(',', env('TRUSTED_HOSTS', 'calaia.ph,www.calaia.ph'))))
    )),

    /*
    |--------------------------------------------------------------------------
    | Safe Redirect Hosts
    |--------------------------------------------------------------------------
    |
    | Absolute "intended" URLs are allowed only for owned hosts. Relative paths
    | remain preferred for normal application redirects.
    |
    */

    'allowed_redirect_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('ALLOWED_REDIRECT_HOSTS', 'calaia.ph,www.calaia.ph'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Session Idle Timeout
    |--------------------------------------------------------------------------
    |
    | This controls how many idle minutes any authenticated customer or admin
    | may leave their account open before a fresh login is required.
    |
    */

    'session_idle_timeout_minutes' => (int) env('SESSION_IDLE_TIMEOUT_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Challenge Timeout
    |--------------------------------------------------------------------------
    |
    | A user must complete the second authentication step within this many
    | minutes after primary authentication. Page refreshes do not extend it.
    |
    */

    'two_factor_challenge_timeout_minutes' => max(1, (int) env('TWO_FACTOR_CHALLENGE_TIMEOUT_MINUTES', 5)),

    /*
    |--------------------------------------------------------------------------
    | WWW Redirect Map
    |--------------------------------------------------------------------------
    |
    | Hosts that should be redirected to their non-www equivalent.
    |
    */

    'www_redirect_map' => env('WWW_REDIRECT_MAP', 'www.calaia.ph'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Logging
    |--------------------------------------------------------------------------
    |
    | This option controls whether to log rate limiting events.
    |
    */

    'log_rate_limits' => env('LOG_RATE_LIMITS', true),

    /*
    |--------------------------------------------------------------------------
    | Security Events Logging
    |--------------------------------------------------------------------------
    |
    | This option controls whether to log security events.
    |
    */

    'log_security_events' => env('LOG_SECURITY_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | XSS Protection Logging
    |--------------------------------------------------------------------------
    |
    | This option controls whether to log XSS attempt events.
    |
    */

    'log_xss_attempts' => env('LOG_XSS_ATTEMPTS', true),

];
