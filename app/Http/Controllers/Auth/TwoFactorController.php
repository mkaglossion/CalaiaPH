<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use App\Services\TwoFactorSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactor,
        private TwoFactorSessionService $session
    ) {
    }

    public function setup(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($this->twoFactor->hasEnabledTwoFactor($user)) {
            return $this->session->isVerified($request, $user)
                ? $this->session->redirectToIntended($request, $user)
                : redirect()->route('two-factor.challenge');
        }

        $secret = $request->session()->get(TwoFactorSessionService::SETUP_KEY);

        if (! is_string($secret) || $secret === '') {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put(TwoFactorSessionService::SETUP_KEY, $secret);
        }

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'provisioningUri' => $this->twoFactor->provisioningUri($user, $secret),
            'maskedAccount' => $this->twoFactor->maskedAccount($user),
            'isAdmin' => $user->user_type === 'admin',
        ]);
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $user = $request->user();
        $secret = $request->session()->get(TwoFactorSessionService::SETUP_KEY);
        $code = $this->codeFromRequest($request);
        $key = $this->rateLimitKey($request, 'setup');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'code' => 'Too many verification attempts. Please wait before trying again.',
            ]);
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'digits' => ['nullable', 'array', 'size:6'],
            'digits.*' => ['nullable', 'regex:/^\d?$/'],
        ]);

        if (! is_string($secret) || $secret === '') {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => 'Your setup session expired. Please scan the new setup code.']);
        }

        if (! $this->twoFactor->verifyCode($secret, $code)) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['code' => 'This verification code is invalid or has expired.'])->withInput();
        }

        RateLimiter::clear($key);
        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $this->twoFactor->encryptSecret($secret),
            'two_factor_recovery_codes' => $this->twoFactor->encryptRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->regenerate();
        $this->session->markVerified($request, $user);
        $request->session()->put(TwoFactorSessionService::PENDING_RECOVERY_CODES, $recoveryCodes);
        $request->session()->put(TwoFactorSessionService::MUST_ACK_RECOVERY_CODES, true);

        return redirect()->route('two-factor.recovery-codes')
            ->with('success', '2FA successfully enabled.');
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->twoFactor->hasEnabledTwoFactor($user)) {
            return redirect()->route('two-factor.setup');
        }

        if ((bool) $request->session()->get(TwoFactorSessionService::MUST_ACK_RECOVERY_CODES, false)) {
            return redirect()->route('two-factor.recovery-codes');
        }

        if ($this->session->isVerified($request, $user)) {
            return $this->session->redirectToIntended($request, $user);
        }

        if ($expiredResponse = $this->expiredChallengeResponse($request)) {
            return $expiredResponse;
        }

        return view('auth.two-factor.challenge', [
            'maskedAccount' => $this->twoFactor->maskedAccount($user),
            'isAdmin' => $user->user_type === 'admin',
            'mode' => 'code',
            ...$this->challengeTiming($request),
        ]);
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->twoFactor->hasEnabledTwoFactor($user)) {
            return redirect()->route('two-factor.setup');
        }

        if ($expiredResponse = $this->expiredChallengeResponse($request)) {
            return $expiredResponse;
        }

        $code = $this->codeFromRequest($request);
        $key = $this->rateLimitKey($request, 'code');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'code' => 'Too many verification attempts. Please wait before trying again.',
            ]);
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'digits' => ['nullable', 'array', 'size:6'],
            'digits.*' => ['nullable', 'regex:/^\d?$/'],
        ]);

        if (! $this->twoFactor->verifyCode($this->twoFactor->decryptSecret($user), $code)) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['code' => 'The verification code is incorrect. Please try again.'])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->session->markVerified($request, $user);

        return $this->session->redirectToIntended($request, $user);
    }

    public function recovery(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->twoFactor->hasEnabledTwoFactor($user)) {
            return redirect()->route('two-factor.setup');
        }

        if ($this->session->isVerified($request, $user)) {
            return $this->session->redirectToIntended($request, $user);
        }

        if ($expiredResponse = $this->expiredChallengeResponse($request)) {
            return $expiredResponse;
        }

        return view('auth.two-factor.challenge', [
            'maskedAccount' => $this->twoFactor->maskedAccount($user),
            'isAdmin' => $user->user_type === 'admin',
            'mode' => 'recovery',
            ...$this->challengeTiming($request),
        ]);
    }

    public function verifyRecovery(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->twoFactor->hasEnabledTwoFactor($user)) {
            return redirect()->route('two-factor.setup');
        }

        if ($expiredResponse = $this->expiredChallengeResponse($request)) {
            return $expiredResponse;
        }

        $key = $this->rateLimitKey($request, 'recovery');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'recovery_code' => 'Too many verification attempts. Please wait before trying again.',
            ]);
        }

        $validated = $request->validate([
            'recovery_code' => ['required', 'string', 'max:32'],
        ]);

        if (! $this->twoFactor->consumeRecoveryCode($user, (string) $validated['recovery_code'])) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'recovery_code' => 'This recovery code is invalid or has already been used.',
            ])->withInput();
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->session->markVerified($request, $user);

        return $this->session->redirectToIntended($request, $user);
    }

    public function recoveryCodes(Request $request): View
    {
        return view('auth.two-factor.recovery-codes', [
            'codes' => $request->session()->get(TwoFactorSessionService::PENDING_RECOVERY_CODES, []),
            'isAdmin' => $request->user()->user_type === 'admin',
            'mustAcknowledge' => (bool) $request->session()->get(TwoFactorSessionService::MUST_ACK_RECOVERY_CODES, false),
        ]);
    }

    public function downloadRecoveryCodes(Request $request): Response
    {
        $user = $request->user();
        $codes = $request->session()->get(TwoFactorSessionService::PENDING_RECOVERY_CODES, []);

        abort_unless($this->twoFactor->hasEnabledTwoFactor($user), 404);
        abort_unless($this->session->isVerified($request, $user), 403);
        abort_unless(is_array($codes) && count($codes) > 0, 404);

        $content = implode(PHP_EOL, [
            'CalaiaPH Two-Factor Authentication Recovery Codes',
            'Account: '.$this->twoFactor->maskedAccount($user),
            'Generated: '.now()->toDateTimeString(),
            '',
            ...$codes,
            '',
            'Each recovery code can only be used once. Store this file securely.',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="calaia-recovery-codes.txt"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ]);
    }

    public function continueAfterRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'saved_recovery_codes' => ['accepted'],
        ]);

        $request->session()->forget([
            TwoFactorSessionService::PENDING_RECOVERY_CODES,
            TwoFactorSessionService::MUST_ACK_RECOVERY_CODES,
        ]);

        return $this->session->redirectToIntended($request, $request->user());
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($this->twoFactor->hasEnabledTwoFactor($user), 404);
        abort_unless($this->session->isVerified($request, $user), 403);

        $codes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $this->twoFactor->encryptRecoveryCodes($codes),
        ])->save();

        $request->session()->put(TwoFactorSessionService::PENDING_RECOVERY_CODES, $codes);

        return redirect()->route('two-factor.recovery-codes')
            ->with('success', 'New recovery codes generated.');
    }

    private function codeFromRequest(Request $request): string
    {
        if ($request->filled('code')) {
            return (string) $request->input('code');
        }

        $digits = $request->input('digits', []);

        return is_array($digits) ? implode('', $digits) : '';
    }

    private function rateLimitKey(Request $request, string $suffix): string
    {
        return 'two-factor-'.$suffix.'|'.$request->user()->getAuthIdentifier().'|'.$request->ip();
    }

    /**
     * @return array{challengeExpiresAt: int, serverNow: int, challengeRemainingSeconds: int}
     */
    private function challengeTiming(Request $request): array
    {
        $serverNow = now()->timestamp;
        $expiresAt = $this->session->challengeExpiresAt($request) ?? $serverNow;

        return [
            'challengeExpiresAt' => $expiresAt,
            'serverNow' => $serverNow,
            'challengeRemainingSeconds' => max(0, $expiresAt - $serverNow),
        ];
    }

    private function expiredChallengeResponse(Request $request): ?RedirectResponse
    {
        if (! $this->session->challengeHasExpired($request)) {
            return null;
        }

        $isAdmin = $request->user()?->user_type === 'admin';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isAdmin ? 'admin.login' : 'home')
            ->with('error', 'Your two-factor verification session expired. Please log in again.');
    }
}
