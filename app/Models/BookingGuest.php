<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'full_name',
        'guest_type',
        'birth_date',
        'gender',
        'contact_number',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',
        'waiver_acknowledged_at',
        'signed_at',
        'signed_by_user_id',
        'signed_by_name',
        'signed_by_relationship',
        'signature_path',
        'ip_address',
        'user_agent',
        'is_primary_contact',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'waiver_acknowledged_at' => 'datetime',
        'signed_at' => 'datetime',
        'is_primary_contact' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
