<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for valid JSON content type for POST/PUT/PATCH requests.
        // Allow multipart form uploads so admin forms can upload files safely.
        if (
            config('security.require_json_content_type')
            && in_array($request->method(), ['POST', 'PUT', 'PATCH'])
            && !$request->isJson()
            && !$this->isMultipartForm($request)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Request must be JSON formatted',
            ], 400);
        }

        // Block suspicious user agents
        if (config('security.block_suspicious_user_agents')) {
            $userAgent = $request->header('User-Agent');
            if ($this->isSuspiciousUserAgent($userAgent)) {
                if (config('security.log_security_events')) {
                    Log::warning('Suspicious user agent blocked', [
                        'user_agent' => $userAgent,
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }
        }

        // Prevent SQL injection patterns in query parameters
        if (config('security.sql_injection_protection')) {
            if ($this->containsSqlInjection($request)) {
                if (config('security.log_security_events')) {
                    Log::warning('SQL injection attempt blocked', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'parameters' => $request->all(),
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters',
                ], 400);
            }
        }

        // Add security headers to response
        $response = $next($request);
        
        foreach (config('security.security_headers') as $header => $value) {
            $response->headers->add([$header => $value]);
        }

        return $response;
    }

    /**
     * Check if user agent is suspicious.
     *
     * @param  string|null  $userAgent
     * @return bool
     */
    protected function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $suspiciousPatterns = config('security.suspicious_user_agent_patterns');

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request contains SQL injection patterns.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function containsSqlInjection(Request $request): bool
    {
        $parameters = array_merge($request->query->all(), $request->request->all());
        
        $sqlPatterns = config('security.sql_injection_patterns');

        foreach ($parameters as $key => $value) {
            $stringValue = (string) $value;
            
            foreach ($sqlPatterns as $pattern) {
                if (preg_match($pattern, $stringValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if the request is a multipart form upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isMultipartForm(Request $request): bool
    {
        $contentType = (string) $request->header('Content-Type', '');

        return stripos($contentType, 'multipart/form-data') !== false;
    }
}
