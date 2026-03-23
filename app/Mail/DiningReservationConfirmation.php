<?php

namespace App\Mail;

use App\Models\DiningReservation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiningReservationConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(public DiningReservation $reservation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            subject: 'Dining Reservation Confirmed - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dining-reservation-confirmation',
            with: [
                'reservation' => $this->reservation,
                'food' => $this->reservation->food,
                'confirmationNumber' => 'DR' . str_pad($this->reservation->id, 5, '0', STR_PAD_LEFT),
            ],
        );
    }
}
