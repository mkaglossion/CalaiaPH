<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($booking->user_id !== null) {
            return (int) $booking->user_id === (int) $user->id;
        }

        return strtolower(trim((string) $booking->customer_email))
            === strtolower(trim((string) $user->email));
    }
}
