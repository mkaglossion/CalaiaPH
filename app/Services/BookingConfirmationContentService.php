<?php

namespace App\Services;

use App\Models\BookingConfirmationContent;
use Illuminate\Support\Facades\Cache;

class BookingConfirmationContentService
{
    public const CACHE_KEY = 'booking_confirmation_contents_resolved';

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(30), function () {
            $resolved = [];

            foreach (BookingConfirmationContent::allowedTypes() as $type) {
                $resolved[$type] = $this->resolveType($type);
            }

            return $resolved;
        });
    }

    public function forType(string $type, array $context = []): array
    {
        $normalized = BookingConfirmationContent::normalizeType($type);
        $resolved = $this->all()[$normalized] ?? $this->defaultsFor($normalized);

        return $this->applyContextTokens($resolved, $context);
    }

    public function forBookingData(array $bookingData, array $context = []): array
    {
        $type = BookingConfirmationContent::normalizeType(
            (string) ($bookingData['booking_type'] ?? $bookingData['type'] ?? 'dayvisit')
        );

        return $this->forType($type, $context);
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function resolveType(string $type): array
    {
        $defaults = $this->defaultsFor($type);
        $record = BookingConfirmationContent::where('booking_type', $type)->first();

        if (!$record) {
            return $defaults;
        }

        return array_replace_recursive($defaults, [
            'page' => [
                'eyebrow' => $record->page_eyebrow,
                'title' => $record->page_title,
                'description' => $record->page_description,
            ],
            'next_steps' => [
                'title' => $record->next_steps_title,
                'items' => $record->next_steps ?? $defaults['next_steps']['items'],
            ],
            'support' => [
                'title' => $record->support_title,
                'description' => $record->support_description,
                'email' => $record->support_email,
                'phone' => $record->support_phone,
            ],
            'buttons' => [
                'home' => $record->home_button_label,
                'print' => $record->print_button_label,
                'payment' => $record->payment_button_label,
            ],
            'email' => [
                'subject' => $record->email_subject,
                'greeting' => $record->email_greeting,
                'intro' => $record->email_intro,
                'closing' => $record->email_closing,
            ],
        ]);
    }

    private function defaultsFor(string $type): array
    {
        return match ($type) {
            'staycation' => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Staycation',
                    'title' => 'Booking Confirmed!',
                    'description' => 'Your staycation booking has been successfully processed and confirmed.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Check your email for a detailed confirmation and booking instructions.',
                        'Save the date in your calendar so your trip is locked in.',
                        'Contact us if you need to make changes or have any questions.',
                    ],
                ],
                'support' => [
                    'title' => 'Need assistance?',
                    'description' => 'Reach us anytime if you need help with your booking.',
                    'email' => 'calaia.ph@gmail.com',
                    'phone' => '+63 905 129 4741',
                ],
                'buttons' => [
                    'home' => 'Return to Home',
                    'print' => 'Print Confirmation',
                    'payment' => null,
                ],
                'email' => [
                    'subject' => 'Booking Receipt - CalaiaPH',
                    'greeting' => 'Dear',
                    'intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                    'closing' => 'We look forward to welcoming you to CalaiaPH!',
                ],
            ],
            'package' => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Package Booking',
                    'title' => 'Confirm Your Payment',
                    'description' => 'Review the booking summary below before proceeding to secure checkout.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Review your booking details and total amount.',
                        'Proceed to secure checkout to complete payment.',
                        'Keep your reference number for any future support requests.',
                    ],
                ],
                'support' => [
                    'title' => 'Need help with your package booking?',
                    'description' => 'Reach us anytime if you need help with your package payment.',
                    'email' => 'calaia.ph@gmail.com',
                    'phone' => '+63 905 129 4741',
                ],
                'buttons' => [
                    'home' => 'Back to Packages',
                    'print' => null,
                    'payment' => 'Proceed to Payment',
                ],
                'email' => [
                    'subject' => 'Booking Confirmation - {reference}',
                    'greeting' => 'Hi',
                    'intro' => 'Your package booking is confirmed.',
                    'closing' => 'If you need changes to your reservation, contact us with your reference number.',
                ],
            ],
            default => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Day Visit',
                    'title' => 'Day Visit Confirmed!',
                    'description' => 'Your day visit booking has been successfully processed and confirmed.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Check your email for your confirmation and entry details.',
                        'Prepare your booking reference before your visit.',
                        'Contact us if you need to make changes or have any questions.',
                    ],
                ],
                'support' => [
                    'title' => 'Need assistance?',
                    'description' => 'Reach us anytime if you need help with your booking.',
                    'email' => 'calaia.ph@gmail.com',
                    'phone' => '+63 905 129 4741',
                ],
                'buttons' => [
                    'home' => 'Return to Home',
                    'print' => 'Print Confirmation',
                    'payment' => null,
                ],
                'email' => [
                    'subject' => 'Booking Receipt - CalaiaPH',
                    'greeting' => 'Dear',
                    'intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                    'closing' => 'We look forward to welcoming you to CalaiaPH!',
                ],
            ],
        };
    }

    private function applyContextTokens(array $content, array $context): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = $this->applyContextTokens($value, $context);
            } elseif (is_string($value)) {
                $content[$key] = $this->replaceTokens($value, $context);
            }
        }

        return $content;
    }

    private function replaceTokens(string $value, array $context): string
    {
        if ($value === '' || empty($context)) {
            return $value;
        }

        $replace = [];
        foreach ($context as $key => $contextValue) {
            if (is_scalar($contextValue) || $contextValue === null) {
                $replace['{' . $key . '}'] = (string) $contextValue;
            }
        }

        return strtr($value, $replace);
    }
}
