<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\PaymongoWebhookEvent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymongoWebhookCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['booking.paymongo_webhook_retention_days' => 180]);
    }

    public function test_webhook_log_page_only_exposes_cleanup_selection_for_eligible_rows(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $eligible = $this->createWebhookEvent(now()->subDays(181));
        $recent = $this->createWebhookEvent(now()->subDays(30));
        $invalidSignature = $this->createWebhookEvent(now(), [
            'signature_valid' => false,
            'handled' => false,
            'http_status' => 401,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['success' => 'Webhook logs deleted successfully.'])
            ->get(route('admin.bookings.paymongo-webhooks'));

        $response->assertOk();
        $response->assertSee('Cleanup Eligible');
        $response->assertSee('eligible after 180 days');
        $response->assertSee('data-admin-flash-library="toastr"', false);
        $response->assertSee('data-admin-flash-type="success"', false);
        $response->assertSee('Webhook logs deleted successfully.');
        $response->assertSee('value="' . $eligible->id . '"', false);
        $response->assertSee('value="' . $invalidSignature->id . '"', false);
        $response->assertDontSee('value="' . $recent->id . '"', false);
    }

    public function test_bulk_cleanup_deletes_only_eligible_webhook_logs(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $oldDate = now()->subDays(181);

        $eligible = $this->createWebhookEvent($oldDate);
        $recent = $this->createWebhookEvent(now()->subDays(30));
        $unhandled = $this->createWebhookEvent($oldDate, ['handled' => false]);
        $failed = $this->createWebhookEvent($oldDate, ['http_status' => 500]);
        $invalidSignature = $this->createWebhookEvent(now(), [
            'signature_valid' => false,
            'handled' => false,
            'http_status' => 401,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.bookings.paymongo-webhooks.bulk-delete'),
            [
                'event_ids' => [
                    $eligible->id,
                    $recent->id,
                    $unhandled->id,
                    $failed->id,
                    $invalidSignature->id,
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success', fn (string $message) => str_contains($message, 'Deleted 2') && str_contains($message, '3 protected'));
        $this->assertDatabaseMissing('paymongo_webhook_events', ['id' => $eligible->id]);
        $this->assertDatabaseMissing('paymongo_webhook_events', ['id' => $invalidSignature->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $recent->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $unhandled->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $failed->id]);

        $activity = AdminActivityLog::latest()->firstOrFail();
        $this->assertSame('paymongo_webhook_bulk_delete', $activity->action);
        $this->assertSame(2, $activity->changes['deleted_count']);
        $this->assertSame(3, $activity->changes['skipped_count']);
    }

    public function test_bulk_cleanup_rejects_a_selection_with_no_eligible_logs(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $unhandled = $this->createWebhookEvent(now()->subDays(181), ['handled' => false]);

        $response = $this->actingAs($admin)->delete(
            route('admin.bookings.paymongo-webhooks.bulk-delete'),
            ['event_ids' => [$unhandled->id]]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $unhandled->id]);
        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_scheduled_pruning_rule_only_removes_eligible_logs(): void
    {
        $eligible = $this->createWebhookEvent(now()->subDays(181));
        $recent = $this->createWebhookEvent(now()->subDays(30));
        $unhandled = $this->createWebhookEvent(now()->subDays(181), ['handled' => false]);
        $oldInvalidSignature = $this->createWebhookEvent(now()->subDays(31), [
            'signature_valid' => false,
            'handled' => false,
            'http_status' => 401,
        ]);
        $recentInvalidSignature = $this->createWebhookEvent(now()->subDays(7), [
            'signature_valid' => false,
            'handled' => false,
            'http_status' => 401,
        ]);

        $this->artisan('model:prune', [
            '--model' => PaymongoWebhookEvent::class,
        ])->assertSuccessful();

        $this->assertDatabaseMissing('paymongo_webhook_events', ['id' => $eligible->id]);
        $this->assertDatabaseMissing('paymongo_webhook_events', ['id' => $oldInvalidSignature->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $recent->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $unhandled->id]);
        $this->assertDatabaseHas('paymongo_webhook_events', ['id' => $recentInvalidSignature->id]);
    }

    private function createWebhookEvent(CarbonInterface $createdAt, array $overrides = []): PaymongoWebhookEvent
    {
        $event = PaymongoWebhookEvent::create(array_merge([
            'event_type' => 'payment.paid',
            'resource_type' => 'payment',
            'reference' => 'TXN-' . str()->upper(str()->random(8)),
            'payment_id' => 'pay_' . str()->random(12),
            'amount_centavos' => 150000,
            'signature_present' => true,
            'signature_valid' => true,
            'handled' => true,
            'http_status' => 200,
            'message' => 'Webhook processed',
            'payload' => ['data' => ['type' => 'event']],
        ], $overrides));

        $event->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $event->refresh();
    }
}
