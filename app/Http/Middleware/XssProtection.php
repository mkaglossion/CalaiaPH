<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class XssProtection
{
    /**
     * Patterns that indicate XSS attempts
     */
    protected array $xssPatterns = [
        // Script tags
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '/<script\b[^>]*>/i',
        '/<\/script>/i',
        
        // Event handlers
        '/\bon\w+\s*=/i', // onclick=, onload=, onerror=, etc.
        
        // JavaScript protocol
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:/i',
        
        // HTML injection
        '/<iframe\b/i',
        '/<object\b/i',
        '/<embed\b/i',
        '/<applet\b/i',
        '/<meta\b/i',
        '/<link\b/i',
        '/<style\b/i',
        '/<base\b/i',
        
        // Expression injection
        '/expression\s*\(/i',
        '/behavior\s*:/i',
        '/-moz-binding\s*:/i',
        
        // SVG XSS
        '/<svg\b/i',
        '/<math\b/i',
        
        // PHP injection
        '/<\?php/i',
        '/<\?=/i',
        
        // SQL injection patterns (additional)
        '/union\s+select/i',
        '/union\s+all\s+select/i',
        '/select\s+.*\s+from/i',
        '/insert\s+into/i',
        '/delete\s+from/i',
        '/drop\s+table/i',
        '/drop\s+database/i',
        '/truncate\s+table/i',
        '/update\s+.*\s+set/i',
        '/exec\s*\(/i',
        '/execute\s*\(/i',
        '/sp_\w+/i',
        '/xp_\w+/i',
        
        // Path traversal
        '/\.\.\//i',
        '/\.\.\\\/i',
        
        // Command injection
        '/;\s*cat\s/i',
        '/;\s*ls\s/i',
        '/;\s*rm\s/i',
        '/;\s*wget\s/i',
        '/;\s*curl\s/i',
        '/\|\s*cat\s/i',
        '/\|\s*ls\s/i',
        '/`[^`]+`/', // backtick execution
        
        // LDAP injection
        '/\(\|?\(/i',
        '/\)\)/i',
        
        // XML/XXE injection
        '/<!DOCTYPE/i',
        '/<!ENTITY/i',
        '/SYSTEM\s+"/i',
        '/PUBLIC\s+"/i',
    ];

    /**
     * Input fields that should be strictly validated
     */
    protected array $strictValidationFields = [
        'email',
        'phone',
        'contact_number',
        'password',
        'username',
        'name',
        'first_name',
        'last_name',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check all input for XSS patterns
        $input = $request->all();
        
        if ($this->detectXss($input)) {
            if (config('security.log_xss_attempts', true)) {
                Log::warning('XSS attempt detected', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_agent' => $request->userAgent(),
                    'input' => $this->sanitizeForLogging($input),
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid input detected. Please remove any special characters or scripts.',
            ], 400);
        }

        // Sanitize input
        $sanitizedInput = $this->sanitizeInput($input);
        $request->replace($sanitizedInput);

        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Detect XSS patterns in input data
     */
    protected function detectXss(array $data, string $key = ''): bool
    {
        foreach ($data as $k => $value) {
            $currentKey = $key ? "{$key}.{$k}" : $k;
            
            if (is_array($value)) {
                if ($this->detectXss($value, $currentKey)) {
                    return true;
                }
            } elseif (is_string($value)) {
                // URL decode to catch encoded attacks
                $decodedValue = urldecode($value);
                $decodedValue = html_entity_decode($decodedValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                foreach ($this->xssPatterns as $pattern) {
                    if (preg_match($pattern, $value) || preg_match($pattern, $decodedValue)) {
                        return true;
                    }
                }
                
                // Check for null bytes
                if (strpos($value, "\0") !== false) {
                    return true;
                }
                
                // Check for excessive special characters (potential obfuscation)
                $specialCharCount = preg_match_all('/[<>"\'&\[\]{}()]/', $value);
                if ($specialCharCount > 10) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value, $key);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize a string value
     */
    protected function sanitizeString(string $value, string $key): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // For strict validation fields, apply stricter rules
        if (in_array($key, $this->strictValidationFields)) {
            $value = $this->strictSanitize($value, $key);
        }
        
        return $value;
    }

    /**
     * Strict sanitization for specific fields
     */
    protected function strictSanitize(string $value, string $key): string
    {
        switch ($key) {
            case 'email':
                // Only allow valid email characters
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                break;
                
            case 'phone':
            case 'contact_number':
                // Only allow digits, spaces, dashes, parentheses, and plus sign
                $value = preg_replace('/[^0-9\s\-\(\)\+]/', '', $value);
                break;
                
            case 'name':
            case 'first_name':
            case 'last_name':
                // Only allow letters, spaces, hyphens, and apostrophes
                $value = preg_replace('/[^a-zA-Z\s\-\']/', '', $value);
                break;
                
            case 'username':
                // Only allow alphanumeric, underscores, and hyphens
                $value = preg_replace('/[^a-zA-Z0-9_\-]/', '', $value);
                break;
        }
        
        return $value;
    }

    /**
     * Add security headers to response
     */
    protected function addSecurityHeaders(Response $response): void
    {
        // Content Security Policy
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://unpkg.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; ";
        $csp .= "img-src 'self' data: https: blob:; ";
        $csp .= "connect-src 'self' https:; ";
        $csp .= "frame-ancestors 'self'; ";
        $csp .= "base-uri 'self'; ";
        $csp .= "form-action 'self';";
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // X-XSS-Protection (legacy but still useful for older browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions-Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     */
    protected function sanitizeForLogging(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveFields)) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeForLogging($value);
            } else {
                $sanitized[$key] = is_string($value) ? substr($value, 0, 100) : $value;
            }
        }
        
        return $sanitized;
    }
}
