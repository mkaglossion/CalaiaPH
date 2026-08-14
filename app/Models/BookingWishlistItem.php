<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingWishlistItem extends Model
{
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'context_hash',
        'booking_context',
        'selection',
    ];

    protected function casts(): array
    {
        return [
            'booking_context' => 'array',
            'selection' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
