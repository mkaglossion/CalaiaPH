<?php

namespace App\Services;

use App\Contracts\AddOnCheckoutGateway;
use App\Models\AddOnTransaction;
use App\Models\Booking;

class PaymongoAddOnCheckoutGateway implements AddOnCheckoutGateway
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function create(AddOnTransaction $addOn, Booking $booking): object
    {
        return $this->paymentService->createCheckout(
            (int) round((float) $addOn->total_amount * 100),
            'add-on',
            route('profile.add-ons.payment.success', ['add_on' => $addOn->id]) . '&checkout_id={CHECKOUT_ID}',
            route('profile.add-ons.payment.cancelled', ['add_on' => $addOn->id]) . '&checkout_id={CHECKOUT_ID}',
            $addOn->items,
            $addOn->reference,
            $booking->customer_email,
            [
                'record_type' => 'add_on',
                'add_on_transaction_id' => $addOn->id,
                'booking_id' => $booking->id,
                'original_booking_reference' => $booking->transaction_id,
            ]
        );
    }
}
