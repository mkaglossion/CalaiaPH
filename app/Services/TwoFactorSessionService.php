<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorSessionService
{
    public const VERIFIED_USER_ID = 'two_factor.verified_user_id';
    public const INTENDED_URL = 'two_factor.intended';
    public const SETUP_KEY = 'two_factor.setup_secret';
    public const CHALLENGE_STARTED_AT = 'two_factor.challenge_started_at';
    public const PENDING_RECOVERY_CODES = 'two_factor.pending_recovery_codes';
    public const MUST_ACK_RECOVERY_CODES = 'two_factor.must_ack_recovery_codes';
    public const LOGIN_SUCCESS_MESSAGE = 'two_factor.login_success_message';

    public function redirectAfterPrimaryAuthentication(
        Request $request,
        User $user,
        string $fallbackUrl,
        string $successMessage
    ): RedirectResponse
    {
        $request->session()->forget([
            self::VERIFIED_USER_ID,
            self::SETUP_KEY,
            self::CHALLENGE_STARTED_AT,
            self::PENDING_RECOVERY_CODES,
            self::MUST_ACK_RECOVERY_CODES,
            self::LOGIN_SUCCESS_MESSAGE,
        ]);

        $request->session()->put(self::INTENDED_URL, $fallbackUrl);
        $request->session()->put(self::LOGIN_SUCCESS_MESSAGE, $successMessage);

        if ($user->two_factor_confirmed_at !== null) {
            $request->session()->put(self::CHALLENGE_STARTED_AT, now()->timestamp);
        }

        return redirect()->route($user->two_factor_confirmed_at ? 'two-factor.challenge' : 'two-factor.setup');
    }

    public function markVerified(Request $request, User $user): void
    {
        $request->session()->put(self::VERIFIED_USER_ID, $user->getAuthIdentifier());
        $request->session()->forget(self::CHALLENGE_STARTED_AT);
    }

    public function isVerified(Request $request, User $user): bool
    {
        return (string) $request->session()->get(self::VERIFIED_USER_ID) === (string) $user->getAuthIdentifier();
    }

    public function challengeExpiresAt(Request $request): ?int
    {
        $startedAt = $request->session()->get(self::CHALLENGE_STARTED_AT);

        if (! is_numeric($startedAt)) {
            return null;
        }

        $timeoutMinutes = max(1, (int) config('security.two_factor_challenge_timeout_minutes', 5));

        return (int) $startedAt + ($timeoutMinutes * 60);
    }

    public function challengeHasExpired(Request $request): bool
    {
        $expiresAt = $this->challengeExpiresAt($request);

        return $expiresAt === null || now()->timestamp >= $expiresAt;
    }

    public function rememberIntendedUrl(Request $request): void
    {
        if ($request->isMethod('GET')) {
            $request->session()->put(self::INTENDED_URL, $request->fullUrl());
        }
    }

    public function redirectToIntended(Request $request, User $user): RedirectResponse
    {
        $fallback = $user->user_type === 'admin' && route('admin.dashboard', [], false) !== ''
            ? route('admin.dashboard')
            : route('home');

        $redirect = redirect()->to($request->session()->pull(self::INTENDED_URL, $fallback));
        $successMessage = $request->session()->pull(self::LOGIN_SUCCESS_MESSAGE);

        return is_string($successMessage) && $successMessage !== ''
            ? $redirect->with('success', $successMessage)
            : $redirect;
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([
            self::VERIFIED_USER_ID,
            self::INTENDED_URL,
            self::SETUP_KEY,
            self::CHALLENGE_STARTED_AT,
            self::PENDING_RECOVERY_CODES,
            self::MUST_ACK_RECOVERY_CODES,
            self::LOGIN_SUCCESS_MESSAGE,
        ]);
    }
}
