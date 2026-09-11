<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthenticationRateLimiter
{
    public function key(Request $request, string $identifier, string $context): string
    {
        return Str::transliterate(Str::lower($context.'|'.$identifier.'|'.$request->ip()));
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function hit(string $key, int $decaySeconds): void
    {
        RateLimiter::hit($key, $decaySeconds);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key);
    }
}
