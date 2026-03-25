<?php

namespace App\Mail;

use App\Models\PackageBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PackageBookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PackageBooking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(PackageBooking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking Confirmation - {$this->booking->booking_reference}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.package-booking-confirmation',
            with: [
                'booking' => $this->booking,
                'package' => $this->booking->package,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
