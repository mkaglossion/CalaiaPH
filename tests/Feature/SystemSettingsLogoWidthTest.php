<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsLogoWidthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_desktop_and_mobile_public_header_logo_widths(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.system'))
            ->assertOk()
            ->assertSee('Desktop logo width')
            ->assertSee('Mobile logo width')
            ->assertSee('name="header_logo_width"', false)
            ->assertSee('name="mobile_header_logo_width"', false)
            ->assertSee('min="120"', false)
            ->assertSee('max="420"', false)
            ->assertSee('max="360"', false);

        $setting = SystemSetting::current();
        $setting->logo_url = 'https://res.cloudinary.com/demo/image/upload/sample.png';
        $setting->save();
        SystemSetting::refreshCache();

        $this->actingAs($admin)
            ->put(route('admin.settings.system.update'), array_merge($this->basePayload(), [
                'header_logo_width' => 312,
                'mobile_header_logo_width' => 176,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'header_logo_width' => 312,
            'mobile_header_logo_width' => 176,
        ]);

        $header = view('components.header', [
            'systemSettings' => SystemSetting::refreshCache(),
        ])->render();

        $this->assertSame(2, substr_count($header, 'data-header-logo'));
        $this->assertStringContainsString('data-logo-viewport="desktop"', $header);
        $this->assertStringContainsString('data-logo-viewport="mobile"', $header);
        $this->assertStringContainsString('data-logo-width="312"', $header);
        $this->assertStringContainsString('data-logo-width="176"', $header);
        $this->assertStringContainsString('/upload/e_trim/f_auto', $header);
        $this->assertStringContainsString('width: 312px; max-width: 100%; height: auto;', $header);
        $this->assertStringContainsString('width: min(176px, 100%); height: auto;', $header);
        $this->assertStringNotContainsString('max-h-24', $header);
        $this->assertStringNotContainsString('max-h-16', $header);
    }

    public function test_header_logo_width_is_limited_to_the_safe_admin_range(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.settings.system'))
            ->put(route('admin.settings.system.update'), array_merge($this->basePayload(), [
                'header_logo_width' => 421,
                'mobile_header_logo_width' => 220,
            ]))
            ->assertRedirect(route('admin.settings.system'))
            ->assertSessionHasErrors('header_logo_width');

        $this->assertDatabaseMissing('system_settings', [
            'header_logo_width' => 421,
        ]);
    }

    public function test_mobile_header_logo_width_is_limited_to_the_safe_admin_range(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.settings.system'))
            ->put(route('admin.settings.system.update'), array_merge($this->basePayload(), [
                'header_logo_width' => 220,
                'mobile_header_logo_width' => 361,
            ]))
            ->assertRedirect(route('admin.settings.system'))
            ->assertSessionHasErrors('mobile_header_logo_width');

        $this->assertDatabaseMissing('system_settings', [
            'mobile_header_logo_width' => 361,
        ]);
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
