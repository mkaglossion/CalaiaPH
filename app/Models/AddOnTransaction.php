<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AddOnTransaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_VOIDED = 'voided';
    public const STATUS_EXPIRED = 'expired';
    public const PAYMENT_WINDOW_MINUTES = 60;

    protected $fillable = [
        'booking_id',
        'reference',
        'type',
        'items',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paymongo_checkout_id',
        'checkout_url',
        'expires_at',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (AddOnTransaction $transaction) {
            if (blank($transaction->reference)) {
                $transaction->forceFill([
                    'reference' => sprintf('AO-%s-%05d', $transaction->created_at->format('Y'), $transaction->id),
                ])->saveQuietly();
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function paymentExpiresAt(): ?Carbon
    {
        if ($this->payment_status !== 'pending') {
            return null;
        }

        return $this->expires_at
            ?? $this->created_at?->copy()->addMinutes(self::PAYMENT_WINDOW_MINUTES);
    }

    public function isStalePending(): bool
    {
        $expiresAt = $this->paymentExpiresAt();

        return $this->status === self::STATUS_PENDING
            && $this->payment_status === 'pending'
            && $expiresAt !== null
            && $expiresAt->lte(now());
    }

    public function canResumePayment(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->payment_status === 'pending'
            && (filled($this->checkout_url) || filled($this->paymongo_checkout_id))
            && !$this->isStalePending();
    }

    public function scopeStalePending(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->where(function (Builder $expiry) {
                $expiry->where('expires_at', '<=', now())
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNull('expires_at')
                            ->where('created_at', '<=', now()->subMinutes(self::PAYMENT_WINDOW_MINUTES));
                    });
            });
    }

    public function scopeVisibleToCustomer(Builder $query): Builder
    {
        return $query->where('payment_status', '!=', 'expired');
    }
}
