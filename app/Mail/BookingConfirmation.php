<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Receipt - CalaiaPH',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'bookingData' => $this->data['bookingData'] ?? [],
                'selectedActivities' => $this->data['selectedActivities'] ?? [],
                'grandTotal' => $this->data['grandTotal'] ?? 0,
                'customerInfo' => $this->data['customerInfo'] ?? [],
                'isStaycation' => $this->data['isStaycation'] ?? false,
                'isDayvisit' => $this->data['isDayvisit'] ?? false,
                'isSpecialEvent' => $this->data['isSpecialEvent'] ?? false,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}