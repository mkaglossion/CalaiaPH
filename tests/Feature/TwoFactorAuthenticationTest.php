<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\TwoFactorSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_without_two_factor_is_forced_through_setup(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'username' => 'customer',
            'password' => Hash::make('password'),
        ]);

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.setup'));

        $this->get(route('profile.show'))
            ->assertRedirect(route('two-factor.setup'));

        $setupPage = $this->get(route('two-factor.setup'));

        $setupPage
            ->assertOk()
            ->assertSee('Two-Factor Authentication Setup')
            ->assertSee('data-qr-uri="otpauth://totp/', false)
            ->assertSee('/build/assets/two-factor-setup-', false)
            ->assertSee('name="code"', false)
            ->assertDontSee('name="digits[]"', false);

        $this->assertSame(1, substr_count($setupPage->getContent(), 'name="code"'));

        $secret = session(TwoFactorSessionService::SETUP_SECRET);
        $code = app(TwoFactorService::class)->currentCode($secret);

        $this->postWithCsrf(route('two-factor.setup.confirm'), [
            'code' => $code,
        ])->assertRedirect(route('two-factor.recovery-codes'));

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);

        $this->postWithCsrf(route('two-factor.recovery-codes.continue'), [
            'saved_recovery_codes' => '1',
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Welcome back! You have been logged in successfully.');
    }

    public function test_valid_totp_unlocks_customer_profile_access(): void
    {
        [$user, $secret] = $this->userWithTwoFactor(['email' => 'customer@example.com']);

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->get(route('profile.show'))
            ->assertRedirect(route('two-factor.challenge'));

        $this->postWithCsrf(route('two-factor.challenge.verify'), [
            'code' => app(TwoFactorService::class)->currentCode($secret),
        ])->assertRedirect(route('profile.show'))
            ->assertSessionHas('success', 'Welcome back! You have been logged in successfully.');

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('id="flash-messages"', false);
    }

    public function test_admin_dashboard_cannot_be_opened_before_two_factor_challenge(): void
    {
        [$admin, $secret] = $this->userWithTwoFactor([
            'user_type' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->postWithCsrf(route('admin.login.post'), [
            'username' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('two-factor.challenge'));

        $this->postWithCsrf(route('two-factor.challenge.verify'), [
            'code' => app(TwoFactorService::class)->currentCode($secret),
        ])->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success', 'Welcome back, admin!');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="flash-messages"', false)
            ->assertSee('Welcome back, admin!');
    }

    public function test_invalid_totp_is_rejected(): void
    {
        $this->userWithTwoFactor(['email' => 'customer@example.com']);

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->postWithCsrf(route('two-factor.challenge.verify'), [
            'code' => '000000',
        ])->assertSessionHasErrors('code');
    }

    public function test_recovery_code_can_be_used_once_only(): void
    {
        [$user, , $codes] = $this->userWithTwoFactor(['email' => 'customer@example.com']);

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->get(route('two-factor.recovery'))
            ->assertOk();

        $this->postWithCsrf(route('two-factor.recovery.verify'), [
            'recovery_code' => $codes[0],
        ])->assertRedirect(route('home'));

        $this->postWithCsrf(route('logout'))
            ->assertRedirect(route('home'));

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->postWithCsrf(route('two-factor.recovery.verify'), [
            'recovery_code' => $codes[0],
        ])->assertSessionHasErrors('recovery_code');
    }

    public function test_social_login_still_requires_two_factor(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'provider_name' => null,
            'provider_id' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn(
            (new SocialiteUser())->setRaw(['email_verified' => true])->map([
                'id' => 'google-123',
                'nickname' => null,
                'name' => 'Customer Example',
                'email' => 'customer@example.com',
                'avatar' => null,
            ])
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']))
            ->assertRedirect(route('two-factor.setup'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    private function userWithTwoFactor(array $attributes = []): array
    {
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();
        $codes = $service->generateRecoveryCodes();
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('password'),
        ], $attributes));

        $user->forceFill([
            'two_factor_secret' => $service->encryptSecret($secret),
            'two_factor_recovery_codes' => $service->encryptRecoveryCodes($codes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [$user, $secret, $codes];
    }
}
