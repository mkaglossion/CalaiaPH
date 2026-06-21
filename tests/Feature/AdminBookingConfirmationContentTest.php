<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingConfirmationContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_confirmation_cms_renders_package_and_camp_waiver_sections_for_admins(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.booking-confirmation.index'));

        $response->assertOk();
        $response->assertSee('Booking Confirmation CMS');
        $response->assertSee("activeTab = 'staycation'", false);
        $response->assertSee("activeTab = 'package'", false);
        $response->assertSee("activeTab = 'camp-waiver'", false);
        $response->assertSee('Package Confirmation');
        $response->assertSee('Page Copy');
        $response->assertSee('Page Header');
        $response->assertSee('Header');
        $response->assertSee('Footer button labels');
        $response->assertSee('Email Copy');
        $response->assertSee('Body 1');
        $response->assertSee('Signature');
        $response->assertSee('Save Package Content');
        $response->assertDontSee('Edit the camp waiver shown to package booking customers before payment.');
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
