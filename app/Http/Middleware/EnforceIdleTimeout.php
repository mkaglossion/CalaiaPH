<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleTimeout
{
    private const SESSION_KEY = 'last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('security.session_idle_timeout_minutes', 30);

        if ($timeoutMinutes <= 0) {
            return $next($request);
        }

        $now = now()->getTimestamp();
        $lastActivityAt = (int) $request->session()->get(self::SESSION_KEY, 0);

        if ($lastActivityAt > 0 && ($now - $lastActivityAt) >= ($timeoutMinutes * 60)) {
            $isAdmin = $user->user_type === 'admin';
            $redirectUrl = $isAdmin ? route('admin.login') : route('home');
            $message = 'Your session expired due to inactivity. Please log in again.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => $redirectUrl,
                ], 401);
            }

            return redirect()->to($redirectUrl)->with('error', $message);
        }

        $request->session()->put(self::SESSION_KEY, $now);

        return $next($request);
    }
}
