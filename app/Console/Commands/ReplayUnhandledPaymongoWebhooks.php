<?php

namespace App\Console\Commands;

use App\Models\PaymongoWebhookEvent;
use App\Services\PaymongoWebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReplayUnhandledPaymongoWebhooks extends Command
{
    protected $signature = 'paymongo:replay-unhandled 
        {--reference= : Replay only webhook logs matching this reference / checkout / payment id}
        {--limit=50 : Maximum number of webhook events to replay}
        {--dry-run : Show candidate events without replaying them}';

    protected $description = 'Replay successful but unhandled PayMongo webhook logs to recover booking payment states';

    public function handle(): int
    {
        $query = PaymongoWebhookEvent::query()
            ->where('http_status', 200)
            ->where('signature_valid', true)
            ->where('handled', false)
            ->whereIn('event_type', [
                'payment.paid',
                'payment.refunded',
                'checkout_session.payment.paid',
                'checkout_session.payment_success',
            ])
            ->oldest('created_at');

        $reference = trim((string) $this->option('reference'));
        if ($reference !== '') {
            $query->where(function ($builder) use ($reference) {
                $builder->where('reference', 'like', '%' . $reference . '%')
                    ->orWhere('checkout_session_id', 'like', '%' . $reference . '%')
                    ->orWhere('payment_id', 'like', '%' . $reference . '%');
            });
        }

        $limit = max(1, (int) $this->option('limit'));
        $events = $query->limit($limit)->get();

        if ($events->isEmpty()) {
            $this->info('No matching unhandled PayMongo webhook events found.');
            return self::SUCCESS;
        }

        $this->info('Found ' . $events->count() . ' webhook event(s) to inspect.');

        if ($this->option('dry-run')) {
            foreach ($events as $event) {
                $this->line(sprintf(
                    '[dry-run] #%d %s ref=%s checkout=%s payment=%s received=%s',
                    $event->id,
                    $event->event_type ?? 'unknown',
                    $event->reference ?? '-',
                    $event->checkout_session_id ?? '-',
                    $event->payment_id ?? '-',
                    optional($event->created_at)->toDateTimeString() ?? '-'
                ));
            }

            return self::SUCCESS;
        }

        $recovered = 0;
        $stillUnhandled = 0;

        foreach ($events as $event) {
            try {
                $handled = PaymongoWebhookService::handleWebhook($event->payload ?? []);

                if ($handled) {
                    $event->forceFill([
                        'handled' => true,
                        'message' => 'Webhook replayed successfully',
                        'error_message' => null,
                    ])->save();
                    $recovered++;
                    $this->line("Recovered webhook #{$event->id} ({$event->event_type})");
                } else {
                    $event->forceFill([
                        'message' => 'Webhook replay attempted but still unhandled',
                    ])->save();
                    $stillUnhandled++;
                    $this->warn("Still unhandled: webhook #{$event->id} ({$event->event_type})");
                }
            } catch (\Throwable $e) {
                $stillUnhandled++;
                $event->forceFill([
                    'message' => 'Webhook replay failed',
                    'error_message' => $e->getMessage(),
                ])->save();

                Log::error('Failed replaying PayMongo webhook event', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Replay failed for webhook #{$event->id}: {$e->getMessage()}");
            }
        }

        $this->info("Recovered: {$recovered}");
        $this->info("Still unhandled: {$stillUnhandled}");

        return self::SUCCESS;
    }
}
