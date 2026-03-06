<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        // Response should be successful (either 200 or redirect)
        $response->assertStatus(200);
    }

    /**
     * Test forgot password with invalid email.
     */
    public function test_forgot_password_with_invalid_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Laravel default: shows error for invalid email
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test forgot password validation - email required.
     */
    public function test_forgot_password_email_required(): void
    {
        $response = $this->post('/forgot-password', []);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test forgot password validation - valid email format.
     */
    public function test_forgot_password_email_must_be_valid_format(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
