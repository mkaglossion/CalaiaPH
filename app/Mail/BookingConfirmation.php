<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $bookingData;
    public $selectedActivities;
    public $selectedFoods;
    public $staycations;
    public $foods;
    public $grandTotal;
    public $customerInfo;
    public $isStaycation;
    public $isDayvisit;
    public $isSpecialEvent;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->bookingData = $data['bookingData'] ?? [];
        $this->selectedActivities = $data['selectedActivities'] ?? [];
        $this->selectedFoods = $data['selectedFoods'] ?? [];
        $this->staycations = $data['staycations'] ?? collect();
        $this->foods = $data['foods'] ?? collect();
        $this->grandTotal = $data['grandTotal'] ?? 0;
        $this->customerInfo = $data['customerInfo'] ?? [];
        $this->isStaycation = $data['isStaycation'] ?? false;
        $this->isDayvisit = $data['isDayvisit'] ?? false;
        $this->isSpecialEvent = $data['isSpecialEvent'] ?? false;
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
                'bookingData' => $this->bookingData,
                'selectedActivities' => $this->selectedActivities,
                'selectedFoods' => $this->selectedFoods,
                'staycations' => $this->staycations,
                'foods' => $this->foods,
                'grandTotal' => $this->grandTotal,
                'customerInfo' => $this->customerInfo,
                'isStaycation' => $this->isStaycation,
                'isDayvisit' => $this->isDayvisit,
                'isSpecialEvent' => $this->isSpecialEvent,
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
