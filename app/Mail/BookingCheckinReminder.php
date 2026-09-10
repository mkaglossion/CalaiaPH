<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\AdminBookingEmailRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCheckinReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $adminRecipients = app(AdminBookingEmailRecipientService::class)
            ->recipients($booking->customer_email);

        if ($adminRecipients !== []) {
            $this->bcc($adminRecipients);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Upcoming CalaiaPH Check-in - ' . $this->booking->checkin_date->format('F j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-checkin-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
