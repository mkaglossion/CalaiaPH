<?php

namespace Tests\Feature;

use App\Models\BookingConfirmationContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingConfirmationContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_confirmation_cms_hides_camp_waiver_tab_for_admins(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.booking-confirmation.index'));

        $response->assertOk();
        $response->assertSee('Booking Confirmation CMS');
        $response->assertSee("activeTab = 'staycation'", false);
        $response->assertSee("activeTab = 'package'", false);
        $response->assertDontSee("activeTab = 'camp-waiver'", false);
        $response->assertSee('Package Confirmation');
        $response->assertSee('Page Copy');
        $response->assertSee('Page Header');
        $response->assertSee('Header');
        $response->assertSee('Footer button labels');
        $response->assertSee('Email Copy');
        $response->assertSee('Body 1');
        $response->assertSee('Signature');
        $response->assertSee('Save Package Content');
        $response->assertSee('Camp waiver email note');
        $response->assertSee('Waiver form link');
        $response->assertDontSee('Camp Waiver Confirmation');
        $response->assertDontSee('Open Camp Waiver Modal');
        $response->assertDontSee('Edit the waiver that stays separate from the staycation confirmation copy.');
        $response->assertDontSee('Edit the camp waiver shown to package booking customers before payment.');
    }

    public function test_admin_can_update_staycation_email_waiver_note_and_form_link(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.booking-confirmation.update', 'staycation'), [
            'contents' => [
                'staycation' => [
                    'page_eyebrow' => 'CalaiaPH Staycation',
                    'page_title' => 'Booking Confirmed!',
                    'page_description' => 'Your staycation booking is confirmed.',
                    'next_steps_title' => 'What\'s Next?',
                    'next_steps' => "Check your email.\nPrepare your waiver.",
                    'support_title' => 'Need assistance?',
                    'support_description' => 'Contact our team.',
                    'home_button_label' => 'Return to Home',
                    'print_button_label' => 'Print Confirmation',
                    'payment_button_label' => '',
                    'camp_waiver_title' => 'Custom Camp Waiver Reminder',
                    'camp_waiver_body' => "Admin custom waiver instruction.\nOne form per guest.",
                    'camp_waiver_form_url' => 'https://form.jotform.com/calaia/custom-waiver',
                    'email_subject' => 'Booking Receipt - CalaiaPH',
                    'email_greeting' => 'Dear',
                    'email_intro' => 'Your booking is confirmed.',
                    'email_closing' => 'See you soon.',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('booking_confirmation_contents', [
            'booking_type' => 'staycation',
            'camp_waiver_title' => 'Custom Camp Waiver Reminder',
            'camp_waiver_body' => "Admin custom waiver instruction.\nOne form per guest.",
            'camp_waiver_form_url' => 'https://form.jotform.com/calaia/custom-waiver',
        ]);

        $content = app(\App\Services\BookingConfirmationContentService::class)->forType('staycation');

        $this->assertSame('Custom Camp Waiver Reminder', $content['camp_waiver']['title']);
        $this->assertSame('https://form.jotform.com/calaia/custom-waiver', $content['camp_waiver']['form_url']);
        $this->assertSame(['Check your email.', 'Prepare your waiver.'], BookingConfirmationContent::firstWhere('booking_type', 'staycation')->next_steps);
    }

    public function test_booking_confirmation_preview_renders_actual_customer_views_for_admins(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $staycationPreview = $this->actingAs($admin)->get(route('admin.booking-confirmation.preview', ['tab' => 'staycation']));
        $staycationPreview->assertOk();
        $staycationPreview->assertSee('Booking Confirmed!');
        $staycationPreview->assertSee('Return to Home');

        $packagePreview = $this->actingAs($admin)->get(route('admin.booking-confirmation.preview', ['tab' => 'package']));
        $packagePreview->assertOk();
        $packagePreview->assertSee('Confirm Your Payment');
        $packagePreview->assertSee('Proceed to Payment');

        $campWaiverPreview = $this->actingAs($admin)->get(route('admin.booking-confirmation.preview', ['tab' => 'camp-waiver']));
        $campWaiverPreview->assertOk();
        $campWaiverPreview->assertSee('Camp Waiver');
        $campWaiverPreview->assertSee('Print Camp Waiver');
    }
}
