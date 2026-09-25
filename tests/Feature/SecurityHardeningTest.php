<?php

namespace Tests\Feature;

use App\Http\Controllers\CartController;
use App\Mail\PasswordReset;
use App\Models\Booking;
use App\Models\Product;
use App\Models\User;
use App\Services\IdVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('customer-login|missing@example.com|127.0.0.1');
        RateLimiter::clear('admin-login|customer@example.com|127.0.0.1');
        RateLimiter::clear('password-reset|missing@example.com|127.0.0.1');
    }

    public function test_customer_login_uses_generic_errors_and_rate_limits_failures(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postWithCsrf(route('login.submit'), [
                'username' => 'missing@example.com',
                'password' => 'wrongpass',
            ])->assertRedirect(route('home'))
                ->assertSessionHas('error', 'These credentials do not match our records.');
        }

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'missing@example.com',
            'password' => 'wrongpass',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Too many login attempts.'));
    }

    public function test_successful_customer_login_regenerates_session_and_clears_failures(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'username' => 'customer',
            'password' => Hash::make('password'),
        ]);

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'wrongpass',
        ])->assertSessionHas('error', 'These credentials do not match our records.');

        $this->postWithCsrf(route('login.submit'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.setup'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, RateLimiter::attempts('customer-login|customer@example.com|127.0.0.1'));
    }

    public function test_admin_login_does_not_reveal_customer_accounts(): void
    {
        User::factory()->create([
            'user_type' => 'client',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postWithCsrf(route('admin.login.post'), [
            'username' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'These credentials do not match an admin account.');

        $this->assertGuest();
    }

    public function test_password_reset_does_not_reveal_unknown_email_and_throttles_abuse(): void
    {
        Mail::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postWithCsrf(route('forgot.password.post'), [
                'email' => 'missing@example.com',
            ])->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'If an account exists for this email address, a password reset link has been sent.',
                ]);
        }

        $this->postWithCsrf(route('forgot.password.post'), [
            'email' => 'missing@example.com',
        ])->assertStatus(429)
            ->assertJsonPath('success', false);

        Mail::assertNothingSent();
    }

    public function test_password_reset_sends_mail_for_existing_user_without_changing_the_response_shape(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'customer@example.com']);

        $this->postWithCsrf(route('forgot.password.post'), [
            'email' => 'customer@example.com',
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'If an account exists for this email address, a password reset link has been sent.',
            ]);

        $this->assertNotEmpty($user->fresh()->password_reset_token);
        Mail::assertSent(PasswordReset::class);
    }

    public function test_password_reset_logs_out_the_current_user_session(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('old-password'),
            'password_reset_token' => 'reset-token',
            'password_reset_expires' => now()->addHour(),
        ]);

        $this->actingAs($user)
            ->postWithCsrf(route('reset.password.post'), [
                'token' => 'reset-token',
                'email' => 'customer@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertGuest();
        $this->assertNull($user->fresh()->password_reset_token);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_logout_requires_post_and_csrf(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('logout'))
            ->assertMethodNotAllowed();

        $csrfMiddleware = new \App\Http\Middleware\VerifyCsrfToken(app(), app('encrypter'));
        $except = (new ReflectionClass($csrfMiddleware))->getProperty('except');
        $except->setAccessible(true);
        $this->assertSame([], $except->getValue($csrfMiddleware));

        $this->actingAs($user)
            ->postWithCsrf(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_homepage_uses_root_url_and_legacy_home_redirects_permanently(): void
    {
        $this->assertSame(url('/'), route('home'));

        $this->get('/')
            ->assertOk();

        $this->get('/home')
            ->assertRedirect('/')
            ->assertStatus(301);

        $this->postWithCsrf('/home')
            ->assertMethodNotAllowed();
    }

    public function test_package_checkout_validation_does_not_echo_the_public_token(): void
    {
        $response = $this->postJson('/api/payment/create-checkout', [
            'booking_id' => 'invalid',
            'token' => 'sensitive-public-token',
        ]);

        $response->assertUnprocessable()
            ->assertDontSee('sensitive-public-token')
            ->assertJsonMissingPath('received_data');
    }

    public function test_special_event_write_api_requires_an_admin(): void
    {
        $payload = [
            'name' => 'Secure Event',
            'description' => 'Admin-only event content.',
            'features' => 'Feature one',
        ];

        $this->postJson('/api/special-events', $payload)
            ->assertUnauthorized();

        $customer = User::factory()->create(['user_type' => 'client']);
        $this->actingAs($customer)
            ->postJson('/api/special-events', $payload)
            ->assertForbidden();

        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin)
            ->postJson('/api/special-events', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('events', ['name' => 'Secure Event']);
    }

    public function test_oauth_callback_rejects_external_intended_url(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'provider_name' => null,
            'provider_id' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($this->fakeSocialiteUser());
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->withSession(['url.intended' => 'https://evil-example.com/phish'])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']))
            ->assertRedirect(route('two-factor.setup'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_oauth_callback_allows_owned_https_intended_url_as_internal_path(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'provider_name' => null,
            'provider_id' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($this->fakeSocialiteUser());
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->withSession(['url.intended' => 'https://calaia.ph/packages?from=oauth'])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'state']))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_store_cart_ignores_browser_submitted_price_on_add_and_checkout(): void
    {
        $product = Product::create([
            'title' => 'Calaia Tomato Pack',
            'description' => 'Fresh pack',
            'price' => 5000,
            'image' => 'products/tomato.jpg',
            'category' => 'fresh',
            'is_active' => true,
            'stock_quantity' => 10,
            'is_inventory_managed' => true,
        ]);

        $this->postWithCsrf(route('cart.add'), [
            'id' => $product->id,
            'title' => 'Tampered Title',
            'price' => 1,
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $cart = session('cart');
        $item = reset($cart);
        $this->assertSame('Calaia Tomato Pack', $item['title']);
        $this->assertSame(5000.0, $item['price']);

        $this->app->bind(CartController::class, function () {
            return new class extends CartController {
                protected function createPaymongoCheckout(int $amount, string $email, string $name, string $transactionId, array $summary): array
                {
                    return [
                        'id' => 'cs_test_shop',
                        'checkout_url' => 'https://paymongo.test/checkout/cs_test_shop',
                    ];
                }
            };
        });

        $this->postWithCsrf(route('checkout.submit'), [
            'name' => 'Customer Example',
            'email' => 'customer@example.com',
            'phone' => '09171234567',
            'delivery_option' => 'pickup',
            'booking_date' => now()->addDay()->toDateString(),
        ])->assertRedirect('https://paymongo.test/checkout/cs_test_shop');

        $this->assertDatabaseHas('shop_orders', [
            'customer_email' => 'customer@example.com',
            'original_total' => 5000,
            'final_total' => 5000,
        ]);
        $this->assertSame(9, $product->fresh()->stock_quantity);
    }

    public function test_public_web_responses_include_safe_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()')
            ->assertHeader('Content-Security-Policy-Report-Only');
    }

    public function test_report_only_csp_does_not_allow_every_https_origin_or_unsafe_eval(): void
    {
        $response = $this->get(route('home'));
        $policy = (string) $response->headers->get('Content-Security-Policy-Report-Only');

        $this->assertStringContainsString("default-src 'self';", $policy);
        $this->assertStringContainsString("object-src 'none';", $policy);
        $this->assertStringContainsString('https://res.cloudinary.com', $policy);
        $this->assertStringNotContainsString("default-src 'self' https:", $policy);
        $this->assertStringNotContainsString("'unsafe-eval'", $policy);
    }

    public function test_id_verification_uses_private_temporary_storage_and_hides_internal_errors(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $service = Mockery::mock(IdVerificationService::class);
        $service->shouldReceive('verifyIdImage')
            ->once()
            ->andThrow(new \RuntimeException('internal-provider-secret'));
        $this->app->instance(IdVerificationService::class, $service);

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\ApiRateLimiter::class,
            \App\Http\Middleware\ApiSecurity::class,
            \App\Http\Middleware\XssProtection::class,
        ])->postWithCsrf(route('api.id-verification.verify'), [
            'id_image' => UploadedFile::fake()->image('id.jpg', 800, 500),
            'id_number' => 'PWD-123456',
            'id_type' => 'pwd',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('message', 'Verification is temporarily unavailable. Please try again.')
            ->assertDontSee('internal-provider-secret');

        $this->assertSame([], Storage::disk('local')->allFiles('temp_id_verification'));
        $this->assertSame([], Storage::disk('public')->allFiles('temp_id_verification'));
    }

    public function test_base64_id_verification_rejects_non_image_payloads(): void
    {
        Storage::fake('local');

        $response = $this->withoutMiddleware([
            \App\Http\Middleware\ApiRateLimiter::class,
            \App\Http\Middleware\ApiSecurity::class,
            \App\Http\Middleware\XssProtection::class,
        ])->postWithCsrf(route('api.id-verification.verify-base64'), [
            'id_image_base64' => base64_encode('not-an-image'),
            'id_number' => 'PWD-123456',
            'id_type' => 'pwd',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid image data');

        $this->assertSame([], Storage::disk('local')->allFiles('temp_id_verification'));
    }

    public function test_customer_cannot_check_in_another_customers_booking(): void
    {
        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $otherCustomer = User::factory()->create(['email' => 'other@example.com']);
        $booking = Booking::create([
            'user_id' => $otherCustomer->id,
            'transaction_id' => 'TXN-SECURITY-1',
            'customer_name' => 'Other Customer',
            'customer_email' => 'other@example.com',
            'customer_phone' => '09170000000',
            'booking_type' => 'staycation',
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'booking_data' => [],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $this->actingAs($customer)
            ->postWithCsrf(route('checkinout.checkin', $booking))
            ->assertForbidden();
    }

    private function fakeSocialiteUser(): SocialiteUser
    {
        return (new SocialiteUser())
            ->setRaw(['email_verified' => true])
            ->map([
                'id' => 'google-123',
                'nickname' => null,
                'name' => 'Customer Example',
                'email' => 'customer@example.com',
                'avatar' => null,
            ]);
    }
}
