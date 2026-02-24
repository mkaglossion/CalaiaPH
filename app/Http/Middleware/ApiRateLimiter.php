<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $config = config("rate-limits.{$limiter}");
        
        if (!$config) {
            $limiter = 'api';
            $config = config('rate-limits.api');
        }

        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) $config['max_attempts'];
        $decaySeconds = (int) ($config['decay_seconds'] ?? 60); // Already in seconds

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            if (config('security.log_rate_limits')) {
                Log::warning('Rate limit exceeded', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'limiter' => $limiter,
                    'max_attempts' => $maxAttempts,
                    'retry_after' => $retryAfter,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
                'rate_limit' => $maxAttempts,
                'rate_limit_reset' => $retryAfter,
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);
        
        $response = $next($request);

        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $maxAttempts - RateLimiter::attempts($key),
            'X-RateLimit-Reset' => now()->addSeconds((int) RateLimiter::availableIn($key))->getTimestamp(),
        ]);

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $ip = $request->ip();
        $method = $request->method();
        $path = $request->path();
        
        return Str::slug("{$ip}|{$method}|{$path}");
    }
}
