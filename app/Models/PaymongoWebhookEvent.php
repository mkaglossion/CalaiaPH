<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymongoWebhookEvent extends Model
{
    use HasFactory;

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
}

