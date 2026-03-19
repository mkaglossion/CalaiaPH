<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_low_inventory_alerts',
        'email_pending_payments',
        'email_bookings',
        'email_user_signups',
        'theme_preference',
        'language',
        'dashboard_widgets',
    ];

    protected $casts = [
        'email_low_inventory_alerts' => 'boolean',
        'email_pending_payments' => 'boolean',
        'email_bookings' => 'boolean',
        'email_user_signups' => 'boolean',
        'dashboard_widgets' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
