<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageBooking extends Model
{
    protected $fillable = [
        'booking_reference',
        'package_id',
        'booking_date',
        'session',
        'total_guests',
        'first_name',
        'last_name',
        'email',
        'phone',
        'special_requests',
        'total_amount',
        'status',
        'payment_status',
        'paymongo_reference',
        'payment_reference'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the package for this booking
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
