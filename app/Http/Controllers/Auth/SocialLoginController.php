<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

class SocialLoginController extends Controller
{
    private const SOCIAL_INTENDED_KEY = 'social_login.intended';

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        return $this->redirectToProvider($request, 'google');
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        return $this->handleProviderCallback($request, 'google');
    }

    public function redirectToFacebook(Request $request): RedirectResponse
    {
        return $this->redirectToProvider($request, 'facebook');
    }

    public function handleFacebookCallback(Request $request): RedirectResponse
    {
        return $this->handleProviderCallback($request, 'facebook');
    }

    private function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        if (! $this->providerIsConfigured($provider)) {
            Log::warning('Social login provider is not configured.', [
                'provider' => $provider,
            ]);

            return $this->redirectWithError(
                $request,
                $this->providerLabel($provider).' login is not configured yet. Please contact support.'
            );
        }

        $providerOriginRedirect = $this->redirectToConfiguredProviderOrigin($request, $provider);

        if ($providerOriginRedirect !== null) {
            return $providerOriginRedirect;
        }

        $intendedUrl = $this->sanitizePublicUrl($request, $request->query('intended'));

        if ($intendedUrl !== null) {
            $request->session()->put(self::SOCIAL_INTENDED_KEY, $intendedUrl);
            $request->session()->put('url.intended', $intendedUrl);
        }

        try {
            /** @var AbstractProvider $socialiteProvider */
            $socialiteProvider = Socialite::driver($provider);

            $socialiteProvider->scopes($this->providerScopes($provider));

            if ($provider === 'google') {
                $socialiteProvider->with(['prompt' => 'select_account']);
            }

            return $socialiteProvider->redirect();
        } catch (Throwable $exception) {
            Log::warning('Unable to start social login redirect.', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectWithError(
                $request,
                'Unable to start '.$this->providerLabel($provider).' login right now. Please try again.'
            );
        }
    }

    private function providerIsConfigured(string $provider): bool
    {
        return filled(config('services.'.$provider.'.client_id'))
            && filled(config('services.'.$provider.'.client_secret'))
            && filled(config('services.'.$provider.'.redirect'));
    }

    private function redirectToConfiguredProviderOrigin(Request $request, string $provider): ?RedirectResponse
    {
        $redirectUri = (string) config('services.'.$provider.'.redirect');
        $scheme = strtolower((string) parse_url($redirectUri, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($redirectUri, PHP_URL_HOST));

        if ($scheme === '' || $host === '') {
            return null;
        }

        $port = parse_url($redirectUri, PHP_URL_PORT);
        $usesDefaultPort = ($scheme === 'http' && $port === 80)
            || ($scheme === 'https' && $port === 443);
        $configuredOrigin = $scheme.'://'.$host;

        if ($port !== null && ! $usesDefaultPort) {
            $configuredOrigin .= ':'.$port;
        }

        if (strcasecmp($request->getSchemeAndHttpHost(), $configuredOrigin) === 0) {
            return null;
        }

        return redirect()->away($configuredOrigin.$request->getRequestUri());
    }

    private function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        if ($request->filled('error') || $request->filled('error_reason')) {
            return $this->redirectWithError(
                $request,
                'The '.$this->providerLabel($provider).' sign-in request was cancelled or denied.'
            );
        }

        try {
            /** @var SocialiteUser $socialUser */
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $exception) {
            Log::notice('Social login callback failed state validation.', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectWithError(
                $request,
                'Your '.$this->providerLabel($provider).' login session expired. Please try again.'
            );
        } catch (Throwable $exception) {
            Log::error('Social login callback failed.', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectWithError(
                $request,
                'Unable to complete '.$this->providerLabel($provider).' login. Please try again.'
            );
        }

        $providerId = trim((string) $socialUser->getId());

        if ($providerId === '') {
            return $this->redirectWithError(
                $request,
                'Unable to identify your '.$this->providerLabel($provider).' account.'
            );
        }

        $email = Str::lower((string) $socialUser->getEmail());
        $emailVerified = $this->providerEmailIsVerified($socialUser);
        $user = User::where('provider_name', $provider)
            ->where('provider_id', $providerId)
            ->first();
        $matchedByProvider = $user !== null;

        if ($user === null && $email === '') {
            return $this->redirectWithError(
                $request,
                'Your '.$this->providerLabel($provider).' account did not provide an email address. Please use your regular login or choose another provider.'
            );
        }

        if ($user === null) {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        }

        if (
            $user !== null
            && $user->user_type === 'admin'
            && ! $matchedByProvider
            && ($provider !== 'google' || ! $emailVerified)
        ) {
            return $this->redirectWithError(
                $request,
                'Admin accounts can only use a verified Google account for social login.'
            );
        }

        if ($user !== null) {
            $this->syncExistingUser($user, $provider, $providerId, $socialUser->getAvatar(), $emailVerified);
        } else {
            $user = $this->createCustomerFromSocialProfile($socialUser, $provider, $providerId, $emailVerified, $email);
        }

        PendingUser::where('email', $email)->delete();

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Welcome back! You have been logged in successfully.');
    }

    private function syncExistingUser(
        User $user,
        string $provider,
        string $providerId,
        ?string $avatar,
        bool $emailVerified
    ): void {
        $updates = [
            'provider_name' => $provider,
            'provider_id' => $providerId,
        ];

        if (! blank($avatar) && $avatar !== $user->avatar) {
            $updates['avatar'] = $avatar;
        }

        if ($emailVerified && $user->email_verified_at === null) {
            $updates['email_verified_at'] = now();
        }

        $user->forceFill($updates)->save();
    }

    private function createCustomerFromSocialProfile(
        SocialiteUser $socialUser,
        string $provider,
        string $providerId,
        bool $emailVerified,
        string $email
    ): User {
        $nameParts = $this->extractNameParts($socialUser, $email);

        return User::create([
            'user_type' => 'client',
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'email' => $email,
            'username' => $this->generateUniqueUsername($socialUser, $email),
            'password' => Str::random(40),
            'provider_name' => $provider,
            'provider_id' => $providerId,
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => $emailVerified ? now() : null,
        ]);
    }

    private function extractNameParts(SocialiteUser $socialUser, string $email): array
    {
        $displayName = trim((string) $socialUser->getName());

        if ($displayName === '') {
            $displayName = Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));
        }

        $segments = preg_split('/\s+/', $displayName) ?: [];
        $firstName = $segments[0] ?? 'Guest';
        $lastName = count($segments) > 1 ? trim(implode(' ', array_slice($segments, 1))) : 'User';

        return [
            'first_name' => Str::limit($firstName, 255, ''),
            'last_name' => Str::limit($lastName, 255, ''),
        ];
    }

    private function generateUniqueUsername(SocialiteUser $socialUser, string $email): string
    {
        $baseUsername = $socialUser->getNickname()
            ?: Str::before($email, '@')
            ?: Str::slug((string) $socialUser->getName(), '_');

        $baseUsername = Str::lower((string) $baseUsername);
        $baseUsername = preg_replace('/[^a-z0-9_]/', '_', $baseUsername) ?? 'user';
        $baseUsername = trim((string) $baseUsername, '_');
        $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
        $baseUsername = Str::limit($baseUsername, 40, '');

        $username = $baseUsername;
        $counter = 1;

        while (
            User::where('username', $username)->exists()
            || PendingUser::where('username', $username)->exists()
        ) {
            $suffix = '_'.$counter;
            $username = Str::limit($baseUsername, max(1, 40 - strlen($suffix)), '').$suffix;
            $counter++;
        }

        return $username;
    }

    private function providerEmailIsVerified(SocialiteUser $socialUser): bool
    {
        $raw = $socialUser->getRaw();

        return (bool) (
            data_get($raw, 'email_verified')
            ?? data_get($raw, 'verified_email')
            ?? data_get($raw, 'verified')
        );
    }

    private function providerScopes(string $provider): array
    {
        return $provider === 'google'
            ? ['openid', 'profile', 'email']
            : ['email'];
    }

    private function providerLabel(string $provider): string
    {
        return ucfirst($provider);
    }

    private function redirectWithError(Request $request, string $message): RedirectResponse
    {
        return redirect()->to($this->pullIntendedUrl($request))->with('error', $message);
    }

    private function pullIntendedUrl(Request $request): string
    {
        $intended = $request->session()->pull(self::SOCIAL_INTENDED_KEY)
            ?? $request->session()->get('url.intended');

        $sanitized = $this->sanitizePublicUrl($request, $intended);

        return $sanitized ?? route('home');
    }

    private function sanitizePublicUrl(Request $request, ?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            return null;
        }

        $path = '/'.ltrim((string) ($parsedUrl['path'] ?? '/'), '/');
        $allowedHosts = array_filter([
            parse_url(config('app.url'), PHP_URL_HOST),
            $request->getHost(),
        ]);

        if (isset($parsedUrl['host']) && ! in_array($parsedUrl['host'], $allowedHosts, true)) {
            return null;
        }

        if (
            Str::startsWith($path, '/admin')
            || in_array($path, [
                '/auth/google/redirect',
                '/auth/google/callback',
                '/auth/facebook/redirect',
                '/auth/facebook/callback',
                '/logout',
            ], true)
        ) {
            return null;
        }

        $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';

        return url($path.$query);
    }
}
