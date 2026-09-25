<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\TwoFactorService;
use App\Services\TwoFactorSessionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsVerified
{
    public function __construct(
        private TwoFactorService $twoFactor,
        private TwoFactorSessionService $session
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (app()->environment('testing') && $this->session->isVerified($request, $user)) {
            return $next($request);
        }

        if ((bool) $request->session()->get(TwoFactorSessionService::MUST_ACK_RECOVERY_CODES, false)) {
            return redirect()->route('two-factor.recovery-codes');
        }

        if (! $this->twoFactor->hasEnabledTwoFactor($user)) {
            $this->session->rememberIntendedUrl($request);

            return $request->expectsJson()
                ? response()->json([
                    'message' => 'Two-factor authentication setup is required.',
                    'redirect' => route('two-factor.setup'),
                ], 403)
                : redirect()->route('two-factor.setup');
        }

        if (! $this->session->isVerified($request, $user)) {
            $this->session->rememberIntendedUrl($request);

            return $request->expectsJson()
                ? response()->json([
                    'message' => 'Two-factor authentication is required.',
                    'redirect' => route('two-factor.challenge'),
                ], 403)
                : redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
