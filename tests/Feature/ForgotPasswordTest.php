<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that forgot password page is accessible.
     */
    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /**
     * Test that forgot password form displays required elements.
     */
    public function test_forgot_password_form_displays_email_input(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('email', false);
        $response->assertSee('password', false);
    }

    /**
     * Test forgot password with valid email user exists.
     */
    public function test_forgot_password_with_valid_user_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->postWithCsrf('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk();
    }

    /**
     * Test forgot password with invalid email.
     */
    public function test_forgot_password_with_unknown_email_uses_generic_response(): void
    {
        Mail::fake();

        $response = $this->postWithCsrf('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'If an account exists for this email address, a password reset link has been sent.',
            ]);
        Mail::assertNothingSent();
    }

    /**
     * Test forgot password validation - email required.
     */
    public function test_forgot_password_email_required(): void
    {
        $response = $this->postWithCsrf('/forgot-password', []);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test forgot password validation - valid email format.
     */
    public function test_forgot_password_email_must_be_valid_format(): void
    {
        $response = $this->postWithCsrf('/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
