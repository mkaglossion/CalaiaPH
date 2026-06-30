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

    public function test_google_callback_rejects_admin_account_email(): void
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
        $response->assertSessionHas('error', 'This social login is only available for customer accounts.');
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
