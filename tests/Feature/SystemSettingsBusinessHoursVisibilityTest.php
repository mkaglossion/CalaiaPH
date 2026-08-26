<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsBusinessHoursVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_business_hours_are_retained_but_hidden_from_system_settings(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.system'))
            ->assertOk()
            ->assertSee('data-hidden-system-business-hours class="hidden" aria-hidden="true"', false)
            ->assertSee('name="business_hours_start"', false)
            ->assertSee('name="business_hours_end"', false)
            ->assertSee('Public contact hours are managed in Information &gt; Contact Us.', false);
    }
}
