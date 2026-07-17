<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_facebook_redirect_switches_to_the_configured_callback_origin_before_oauth(): void
    {
        config([
            'services.facebook.client_id' => 'facebook-test-client',
            'services.facebook.client_secret' => 'facebook-test-secret',
            'services.facebook.redirect' => 'https://localhost/auth/facebook/callback',
        ]);

        $response = $this->get('http://127.0.0.1/auth/facebook/redirect');

        $response->assertRedirect('https://localhost/auth/facebook/redirect');
    }

    public function test_facebook_redirect_stays_in_the_app_when_credentials_are_missing(): void
    {
        config([
            'services.facebook.client_id' => '',
            'services.facebook.client_secret' => '',
            'services.facebook.redirect' => 'https://calaia.ph/auth/facebook/callback',
        ]);

        $response = $this->get(route('auth.facebook.redirect'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas(
            'error',
            'Facebook login is not configured yet. Please contact support.'
        );
    }

    public function test_google_redirect_stays_in_the_app_when_credentials_are_missing(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
            'services.google.redirect' => 'https://calaia.ph/auth/google/callback',
        ]);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas(
            'error',
            'Google login is not configured yet. Please contact support.'
        );
    }

    public function test_facebook_oauth_uses_the_secure_configured_callback_uri(): void
    {
        config([
            'services.facebook.client_id' => 'facebook-test-client',
            'services.facebook.client_secret' => 'facebook-test-secret',
            'services.facebook.redirect' => 'https://localhost/auth/facebook/callback',
        ]);

        $response = $this->get('https://localhost/auth/facebook/redirect');
        $location = (string) $response->headers->get('Location');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->assertStringStartsWith('https://www.facebook.com/', $location);
        $this->assertSame('https://localhost/auth/facebook/callback', $query['redirect_uri'] ?? null);
    }

    public function test_google_callback_links_existing_customer_account_and_logs_in(): void
    {
        $user = User::factory()->create([
            'user_type' => 'client',
            'email' => 'customer@example.com',
            'provider_name' => null,
            'provider_id' => null,
            'avatar' => null,
            'email_verified_at' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn(
            $this->fakeSocialiteUser([
                'id' => 'google-123',
                'name' => 'Customer Example',
                'email' => 'customer@example.com',
                'avatar' => 'https://example.com/google-avatar.jpg',
                'raw' => ['email_verified' => true],
            ])
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['url.intended' => route('packages')])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']));

        $response->assertRedirect(route('packages'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google', $user->fresh()->provider_name);
        $this->assertSame('google-123', $user->fresh()->provider_id);
        $this->assertSame('https://example.com/google-avatar.jpg', $user->fresh()->avatar);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_google_callback_links_existing_admin_account_with_verified_email_and_logs_in(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'provider_name' => null,
            'provider_id' => null,
            'email_verified_at' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn(
            $this->fakeSocialiteUser([
                'id' => 'google-admin',
                'name' => 'Admin Example',
                'email' => 'admin@example.com',
                'raw' => ['email_verified' => true],
            ])
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['url.intended' => route('home')])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Welcome back! You have been logged in successfully.');
        $this->assertAuthenticatedAs($admin->fresh());
        $this->assertSame('google', $admin->fresh()->provider_name);
        $this->assertSame('google-admin', $admin->fresh()->provider_id);
        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_google_callback_does_not_link_unverified_email_to_admin_account(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'provider_name' => null,
            'provider_id' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn(
            $this->fakeSocialiteUser([
                'id' => 'unverified-google-admin',
                'name' => 'Admin Example',
                'email' => 'admin@example.com',
                'raw' => ['email_verified' => false],
            ])
        );

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this
            ->withSession(['url.intended' => route('home')])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas(
            'error',
            'Admin accounts can only use a verified Google account for social login.'
        );
        $this->assertGuest();
        $this->assertNull($admin->fresh()->provider_name);
    }

    public function test_facebook_callback_requires_email_for_new_customer_account(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn(
            $this->fakeSocialiteUser([
                'id' => 'facebook-123',
                'name' => 'No Email User',
                'email' => null,
                'raw' => [],
            ])
        );

        Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);

        $response = $this
            ->withSession(['url.intended' => route('home')])
            ->get(route('auth.facebook.callback', ['code' => 'code', 'state' => 'state']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas(
            'error',
            'Your Facebook account did not provide an email address. Please use your regular login or choose another provider.'
        );
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private function fakeSocialiteUser(array $attributes): SocialiteUser
    {
        return (new SocialiteUser())
            ->setRaw($attributes['raw'] ?? [])
            ->map([
                'id' => $attributes['id'],
                'nickname' => $attributes['nickname'] ?? null,
                'name' => $attributes['name'] ?? null,
                'email' => $attributes['email'] ?? null,
                'avatar' => $attributes['avatar'] ?? null,
            ]);
    }
}
