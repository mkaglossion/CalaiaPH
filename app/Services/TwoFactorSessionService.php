<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorSessionService
{
    public const VERIFIED_USER_ID = 'two_factor.verified_user_id';
    public const INTENDED_URL = 'two_factor.intended';
    public const SETUP_SECRET = 'two_factor.setup_secret';
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
            self::SETUP_SECRET,
            self::PENDING_RECOVERY_CODES,
            self::MUST_ACK_RECOVERY_CODES,
            self::LOGIN_SUCCESS_MESSAGE,
        ]);

        $request->session()->put(self::INTENDED_URL, $fallbackUrl);
        $request->session()->put(self::LOGIN_SUCCESS_MESSAGE, $successMessage);

        return redirect()->route($user->two_factor_confirmed_at ? 'two-factor.challenge' : 'two-factor.setup');
    }

    public function markVerified(Request $request, User $user): void
    {
        $request->session()->put(self::VERIFIED_USER_ID, $user->getAuthIdentifier());
    }

    public function isVerified(Request $request, User $user): bool
    {
        return (string) $request->session()->get(self::VERIFIED_USER_ID) === (string) $user->getAuthIdentifier();
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
            self::SETUP_SECRET,
            self::PENDING_RECOVERY_CODES,
            self::MUST_ACK_RECOVERY_CODES,
            self::LOGIN_SUCCESS_MESSAGE,
        ]);
    }
}
