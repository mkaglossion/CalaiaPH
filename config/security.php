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
        'Content-Security-Policy-Report-Only' => "default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'self'",
    ],

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
