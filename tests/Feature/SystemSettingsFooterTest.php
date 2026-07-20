<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_footer_social_connections_and_public_footer_uses_them(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.system'))
            ->assertOk()
            ->assertSeeInOrder(['Footer Content', 'Footer Preview', 'Social Connections'])
            ->assertSee('name="footer_social_heading"', false)
            ->assertSee('name="footer_instagram_url"', false)
            ->assertSee('name="footer_facebook_url"', false)
            ->assertSee('name="footer_messenger_url"', false)
            ->assertSee('name="footer_email"', false);

        $payload = array_merge($this->basePayload(), [
            'footer_social_heading' => 'Connect with Calaia today',
            'footer_instagram_url' => 'https://www.instagram.com/calaia.dynamic/',
            'footer_facebook_url' => 'https://www.facebook.com/calaia.dynamic/',
            'footer_messenger_url' => 'https://m.me/calaia-dynamic',
            'footer_email' => 'hello@calaia.test',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.settings.system.update'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'footer_social_heading' => 'Connect with Calaia today',
            'footer_instagram_url' => 'https://www.instagram.com/calaia.dynamic/',
            'footer_facebook_url' => 'https://www.facebook.com/calaia.dynamic/',
            'footer_messenger_url' => 'https://m.me/calaia-dynamic',
            'footer_email' => 'hello@calaia.test',
        ]);

        $footer = view('components.footer', [
            'systemSettings' => SystemSetting::refreshCache(),
        ])->render();

        $this->assertStringContainsString('Connect with Calaia today', $footer);
        $this->assertStringContainsString('href="https://www.instagram.com/calaia.dynamic/"', $footer);
        $this->assertStringContainsString('href="https://www.facebook.com/calaia.dynamic/"', $footer);
        $this->assertStringContainsString('href="https://m.me/calaia-dynamic"', $footer);
        $this->assertStringContainsString('href="mailto:hello@calaia.test"', $footer);
        $this->assertStringNotContainsString('https://www.instagram.com/calaia.ph/', $footer);
        $this->assertStringNotContainsString('mailto:calaia.ph@gmail.com', $footer);
    }

    public function test_blank_social_settings_hide_the_public_social_banner(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.settings.system.update'), array_merge($this->basePayload(), [
                'footer_social_heading' => '',
                'footer_instagram_url' => '',
                'footer_facebook_url' => '',
                'footer_messenger_url' => '',
                'footer_email' => '',
            ]))
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.settings.system.update'), $this->basePayload())
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'footer_social_heading' => '',
            'footer_instagram_url' => '',
            'footer_facebook_url' => '',
            'footer_messenger_url' => '',
            'footer_email' => '',
        ]);

        $footer = view('components.footer', [
            'systemSettings' => SystemSetting::refreshCache(),
        ])->render();

        $this->assertStringNotContainsString('Social Media Banner', $footer);
        $this->assertStringNotContainsString('bi bi-instagram text-2xl', $footer);
        $this->assertStringNotContainsString('bi bi-facebook text-2xl', $footer);
        $this->assertStringNotContainsString('bi bi-messenger text-2xl', $footer);
        $this->assertStringNotContainsString('bi bi-envelope-fill text-2xl', $footer);
    }

    private function basePayload(): array
    {
        return [
            'app_name' => 'Calaia PH',
            'app_timezone' => 'Asia/Manila',
            'app_currency' => 'PHP',
            'business_hours_start' => '06:00',
            'business_hours_end' => '22:00',
            'enable_new_bookings' => '1',
            'vat_rate' => '0',
            'system_maintenance' => '0',
            'maintenance_message' => '',
            'footer_title' => 'CALAIA FARM',
            'footer_description' => 'Farm footer description.',
        ];
    }
}
