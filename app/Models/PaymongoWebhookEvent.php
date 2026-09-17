<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class PaymongoWebhookEvent extends Model
{
    use HasFactory, MassPrunable;

    protected $fillable = [
        'event_type',
        'resource_type',
        'reference',
        'checkout_session_id',
        'payment_id',
        'amount_centavos',
        'signature_present',
        'signature_valid',
        'handled',
        'http_status',
        'message',
        'error_message',
        'payload',
    ];

    protected $casts = [
        'signature_present' => 'boolean',
        'signature_valid' => 'boolean',
        'handled' => 'boolean',
        'payload' => 'array',
    ];

    public static function retentionDays(): int
    {
        return max(1, (int) config('booking.paymongo_webhook_retention_days', 180));
    }

    public static function invalidRetentionDays(): int
    {
        return max(1, (int) config('booking.paymongo_invalid_webhook_retention_days', 30));
    }

    public function scopeEligibleForCleanup(Builder $query, ?CarbonInterface $cutoff = null): Builder
    {
        $cutoff ??= now()->subDays(static::retentionDays());

        return $query->where(function (Builder $cleanupQuery) use ($cutoff) {
            $cleanupQuery
                ->where(function (Builder $successfulQuery) use ($cutoff) {
                    $successfulQuery
                        ->where('handled', true)
                        ->where('signature_valid', true)
                        ->where('http_status', 200)
                        ->where('created_at', '<=', $cutoff);
                })
                ->orWhere(function (Builder $rejectedQuery) {
                    $rejectedQuery
                        ->where('signature_valid', false)
                        ->where('http_status', 401);
                });
        });
    }

    public function isEligibleForCleanup(?CarbonInterface $cutoff = null): bool
    {
        $cutoff ??= now()->subDays(static::retentionDays());

        $isRejectedUntrustedRequest = !$this->signature_valid && $this->http_status === 401;
        $isRetainedSuccessfulEvent = $this->handled
            && $this->signature_valid
            && $this->http_status === 200
            && $this->created_at !== null
            && $this->created_at->lte($cutoff);

        return $isRejectedUntrustedRequest || $isRetainedSuccessfulEvent;
    }

    public function prunable(): Builder
    {
        $successfulCutoff = now()->subDays(static::retentionDays());
        $invalidCutoff = now()->subDays(static::invalidRetentionDays());

        return static::query()->where(function (Builder $cleanupQuery) use ($successfulCutoff, $invalidCutoff) {
            $cleanupQuery
                ->where(function (Builder $successfulQuery) use ($successfulCutoff) {
                    $successfulQuery
                        ->where('handled', true)
                        ->where('signature_valid', true)
                        ->where('http_status', 200)
                        ->where('created_at', '<=', $successfulCutoff);
                })
                ->orWhere(function (Builder $rejectedQuery) use ($invalidCutoff) {
                    $rejectedQuery
                        ->where('signature_valid', false)
                        ->where('http_status', 401)
                        ->where('created_at', '<=', $invalidCutoff);
                });
        });
    }
}
