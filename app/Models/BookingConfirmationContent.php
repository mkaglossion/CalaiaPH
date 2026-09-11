<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingConfirmationContent extends Model
{
    public const TYPES = ['staycation', 'campwaiver', 'dayvisit', 'package'];

    protected $fillable = [
        'booking_type',
        'page_eyebrow',
        'page_title',
        'page_description',
        'next_steps_title',
        'next_steps',
        'support_title',
        'support_description',
        'support_email',
        'support_phone',
        'home_button_label',
        'print_button_label',
        'payment_button_label',
        'camp_waiver_title',
        'camp_waiver_body',
        'camp_waiver_form_url',
        'email_subject',
        'email_greeting',
        'email_intro',
        'email_closing',
    ];

    protected $casts = [
        'next_steps' => 'array',
    ];

    public static function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $type = preg_replace('/[^a-z0-9]+/', '', $type) ?: $type;

        return match ($type) {
            'staycation' => 'staycation',
            'campwaiver', 'campwaiverform', 'campwaiverandreleasefromliability', 'campwaiverandrelease' => 'campwaiver',
            'package' => 'package',
            'dayvisit', 'daytour', 'regular' => 'dayvisit',
            default => 'dayvisit',
        };
    }

    public static function allowedTypes(): array
    {
        return self::TYPES;
    }
}
