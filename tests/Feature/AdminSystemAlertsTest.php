<?php

namespace Tests\Feature;

use App\Models\AdminSystemAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSystemAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_page_requires_authentication(): void
    {
        $this->get(route('admin.settings.alerts'))
            ->assertRedirect('/login');
    }

    public function test_admin_can_view_filter_and_prioritize_system_alerts(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        AdminSystemAlert::create([
            'type' => 'inventory',
            'severity' => 'warning',
            'message' => 'Warning alert message',
        ]);
        AdminSystemAlert::create([
            'type' => 'payment',
            'severity' => 'critical',
            'message' => 'Critical alert message',
        ]);
        AdminSystemAlert::create([
            'type' => 'package_booking',
            'severity' => 'info',
            'message' => 'Resolved alert message',
            'resolved_at' => now(),
        ]);

        $this->get(route('admin.settings.alerts'))
            ->assertOk()
            ->assertSee('System Alerts')
            ->assertSeeInOrder(['Critical alert message', 'Warning alert message'])
            ->assertViewHas('alerts', function ($alerts) {
                return $alerts->pluck('message')->all() === [
                    'Critical alert message',
                    'Warning alert message',
                ];
            });

        $this->get(route('admin.settings.alerts', ['status' => 'resolved', 'severity' => 'info']))
            ->assertOk()
            ->assertSee('Resolved alert message')
            ->assertViewHas('alerts', function ($alerts) {
                return $alerts->pluck('message')->all() === ['Resolved alert message'];
            });
    }

    public function test_admin_can_resolve_and_clear_old_resolved_alerts(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $unresolved = AdminSystemAlert::create([
            'type' => 'inventory',
            'severity' => 'warning',
            'message' => 'Resolve this alert',
        ]);

        $this->post(route('admin.settings.alerts.resolve', $unresolved))
            ->assertRedirect()
            ->assertSessionHas('success', 'Alert resolved.');

        $this->assertNotNull($unresolved->fresh()->resolved_at);

        $oldResolved = AdminSystemAlert::create([
            'type' => 'payment',
            'severity' => 'info',
            'message' => 'Old resolved alert',
            'resolved_at' => now()->subDays(8),
        ]);
        $recentResolved = AdminSystemAlert::create([
            'type' => 'payment',
            'severity' => 'info',
            'message' => 'Recent resolved alert',
            'resolved_at' => now(),
        ]);

        DB::table('admin_system_alerts')
            ->where('id', $oldResolved->id)
            ->update(['updated_at' => now()->subDays(8)]);

        $this->post(route('admin.settings.alerts.clear'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Cleared old resolved alerts.');

        $this->assertDatabaseMissing('admin_system_alerts', ['id' => $oldResolved->id]);
        $this->assertDatabaseHas('admin_system_alerts', ['id' => $recentResolved->id]);
        $this->assertDatabaseHas('admin_system_alerts', ['id' => $unresolved->id]);
    }
}
