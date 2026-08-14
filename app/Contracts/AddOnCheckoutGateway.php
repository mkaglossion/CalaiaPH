<?php

namespace App\Contracts;

use App\Models\AddOnTransaction;
use App\Models\Booking;

interface AddOnCheckoutGateway
{
    public function create(AddOnTransaction $addOn, Booking $booking): object;
}
