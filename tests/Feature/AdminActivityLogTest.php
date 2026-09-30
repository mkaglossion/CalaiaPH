<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_shows_all_admins_and_supports_filters(): void
    {
        $viewer = User::factory()->create([
            'user_type' => 'admin',
            'first_name' => 'Viewer',
            'last_name' => 'Admin',
        ]);
        $otherAdmin = User::factory()->create([
            'user_type' => 'admin',
            'first_name' => 'Operations',
            'last_name' => 'Admin',
        ]);

        AdminActivityLog::create([
            'user_id' => $viewer->id,
            'action' => 'booking_status_update',
            'description' => 'Updated booking TXN-FIRST',
            'ip_address' => '127.0.0.1',
        ]);
        AdminActivityLog::create([
            'user_id' => $otherAdmin->id,
            'action' => 'package_booking_payment_recorded',
            'description' => 'Recorded payment for package PB-SECOND',
            'ip_address' => '127.0.0.2',
        ]);

        $this->actingAs($viewer)
            ->get(route('admin.settings.activity-log'))
            ->assertOk()
            ->assertSee('Updated booking TXN-FIRST')
            ->assertSee('Recorded payment for package PB-SECOND')
            ->assertSee('Auto-refresh off');

        $this->actingAs($viewer)
            ->get(route('admin.settings.activity-log', [
                'action' => 'package_booking_payment_recorded',
                'admin' => $otherAdmin->id,
                'q' => 'PB-SECOND',
            ]))
            ->assertOk()
            ->assertSee('Recorded payment for package PB-SECOND')
            ->assertDontSee('Updated booking TXN-FIRST');
    }

    public function test_successful_admin_write_is_logged_without_request_payload(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'first_name' => 'Mark',
            'last_name' => 'Admin',
            'email' => 'mark-admin@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.settings.profile.update'), [
                'first_name' => 'Mark Updated',
                'last_name' => 'Admin',
                'email' => 'mark-admin@example.com',
                'mobile_number' => '09171234567',
            ])
            ->assertRedirect();

        $activity = AdminActivityLog::latest()->firstOrFail();

        $this->assertSame($admin->id, $activity->user_id);
        $this->assertSame('admin_update', $activity->action);
        $this->assertSame('Updated Settings Profile', $activity->description);
        $this->assertSame('admin.settings.profile.update', $activity->changes['route'] ?? null);
        $this->assertSame('PUT', $activity->changes['method'] ?? null);
        $this->assertArrayNotHasKey('email', $activity->changes);
        $this->assertArrayNotHasKey('mobile_number', $activity->changes);
    }

    public function test_activity_log_can_be_created_without_manually_supplying_an_id(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $activity = AdminActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'admin_test_action',
            'description' => 'Created without a manually supplied ID',
        ]);

        $this->assertNotNull($activity->id);
        $this->assertGreaterThan(0, $activity->id);
        $this->assertDatabaseHas('admin_activity_logs', [
            'id' => $activity->id,
            'user_id' => $admin->id,
            'action' => 'admin_test_action',
        ]);
    }

    public function test_existing_detailed_activity_prevents_duplicate_generic_log(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $booking = Booking::create([
            'transaction_id' => 'TXN-AUDIT-001',
            'customer_name' => 'Audit Guest',
            'customer_email' => 'audit@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => now()->addWeek()->toDateString(),
            'checkout_date' => now()->addWeek()->addDay()->toDateString(),
            'booking_data' => [],
            'total_amount' => 1500,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.bookings.cancel', $booking->id))
            ->assertRedirect();

        $this->assertDatabaseCount('admin_activity_logs', 1);
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $admin->id,
            'action' => 'booking_status_update',
            'model_type' => Booking::class,
            'model_id' => $booking->id,
        ]);
    }
}
