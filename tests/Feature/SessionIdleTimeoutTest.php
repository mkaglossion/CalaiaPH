<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionIdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_active_customer_session_can_access_account_area(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20 10:00:00'));
        config(['security.session_idle_timeout_minutes' => 30]);

        $customer = User::factory()->create();

        $response = $this->actingAs($customer)
            ->withSession(['last_activity_at' => now()->subMinutes(29)->getTimestamp()])
            ->get(route('profile.show'));

        $response
            ->assertOk()
            ->assertSee('calaia-session-last-activity', false)
            ->assertSessionHas('last_activity_at', now()->getTimestamp());

        $this->assertAuthenticatedAs($customer);
    }

    public function test_idle_customer_session_is_logged_out_before_account_area_loads(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20 10:00:00'));
        config(['security.session_idle_timeout_minutes' => 30]);

        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->withSession(['last_activity_at' => now()->subMinutes(30)->getTimestamp()])
            ->get(route('profile.show'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'Your session expired due to inactivity. Please log in again.');

        $this->assertGuest();
    }

    public function test_idle_admin_session_is_logged_out_before_admin_area_loads(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20 10:00:00'));
        config(['security.session_idle_timeout_minutes' => 30]);

        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->subMinutes(30)->getTimestamp()])
            ->get(route('admin.settings.activity-log'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'Your session expired due to inactivity. Please log in again.');

        $this->assertGuest();
    }

    public function test_idle_authenticated_json_request_receives_login_redirect(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-20 10:00:00'));
        config(['security.session_idle_timeout_minutes' => 30]);

        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->withSession(['last_activity_at' => now()->subMinutes(30)->getTimestamp()])
            ->postJson(route('session.keep-alive'))
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Your session expired due to inactivity. Please log in again.',
                'redirect' => route('home'),
            ]);

        $this->assertGuest();
    }

    public function test_guest_requests_are_not_given_activity_session_state(): void
    {
        config(['security.session_idle_timeout_minutes' => 30]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSessionMissing('last_activity_at');
    }
}
