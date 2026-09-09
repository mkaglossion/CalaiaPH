<?php

namespace App\Services;

class AdminBookingEmailRecipientService
{
    /**
     * Return the official mailbox used for paid booking notifications.
     *
     * @return array<int, string>
     */
    public function recipients(?string $customerEmail = null): array
    {
        $adminEmail = trim((string) config('mail.booking_admin_address'));

        if (
            filter_var($adminEmail, FILTER_VALIDATE_EMAIL) === false
            || strcasecmp($adminEmail, trim((string) $customerEmail)) === 0
        ) {
            return [];
        }

        return [$adminEmail];
    }
}
