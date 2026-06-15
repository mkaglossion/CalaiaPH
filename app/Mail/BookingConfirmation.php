<?php

namespace App\Mail;

use App\Services\BookingConfirmationContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $bookingData;
    public $selectedActivities;
    public $selectedActivityPackages;
    public $selectedFoods;
    public $staycations;
    public $foods;
    public $grandTotal;
    public $customerInfo;
    public $isStaycation;
    public $isDayvisit;
    public $isSpecialEvent;
    public $confirmationContent;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->bookingData = $data['bookingData'] ?? [];
        $this->selectedActivities = $data['selectedActivities'] ?? [];
        $this->selectedActivityPackages = $data['selectedActivityPackages'] ?? [];
        $this->selectedFoods = $data['selectedFoods'] ?? [];
        $this->staycations = $data['staycations'] ?? collect();
        $this->foods = $data['foods'] ?? collect();
        $this->grandTotal = $data['grandTotal'] ?? 0;
        $this->customerInfo = $data['customerInfo'] ?? [];
        $this->isStaycation = $data['isStaycation'] ?? false;
        $this->isDayvisit = $data['isDayvisit'] ?? false;
        $this->isSpecialEvent = $data['isSpecialEvent'] ?? false;
        $this->confirmationContent = $data['confirmationContent'] ?? app(BookingConfirmationContentService::class)
            ->forBookingData($this->bookingData, [
                'reference' => $this->bookingData['transaction_id'] ?? '',
                'package' => $this->bookingData['package'] ?? '',
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->confirmationContent['email']['subject'] ?? 'Booking Receipt - CalaiaPH',
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
                'selectedActivityPackages' => $this->selectedActivityPackages,
                'selectedFoods' => $this->selectedFoods,
                'staycations' => $this->staycations,
                'foods' => $this->foods,
                'grandTotal' => $this->grandTotal,
                'customerInfo' => $this->customerInfo,
                'isStaycation' => $this->isStaycation,
                'isDayvisit' => $this->isDayvisit,
                'isSpecialEvent' => $this->isSpecialEvent,
                'confirmationContent' => $this->confirmationContent,
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
        if (!$this->isStaycation) {
            return [];
        }

        $filename = !empty($this->bookingData['transaction_id'])
            ? 'camp-waiver-' . $this->bookingData['transaction_id'] . '.pdf'
            : 'camp-waiver.pdf';

        return [
            Attachment::fromData(function () {
                return Pdf::loadView('booking.camp-waiver-pdf', $this->campWaiverPdfPayload())
                    ->setPaper('legal', 'portrait')
                    ->output();
            }, $filename)->withMime('application/pdf'),
        ];
    }

    private function campWaiverPdfPayload(): array
    {
        $acceptances = $this->bookingData['acceptances'] ?? [];
        $campWaiverAcceptance = collect($acceptances)->first(function ($agreement) {
            return ($agreement['source'] ?? '') === 'camp_waiver_accepted'
                || strcasecmp((string) ($agreement['label'] ?? ''), 'Camp Waiver and Release from Liability') === 0;
        });

        $customerInfo = $this->bookingData['customer_info'] ?? $this->customerInfo ?? [];

        return [
            'bookingData' => $this->bookingData,
            'customerInfo' => $customerInfo,
            'campWaiverAcceptance' => $campWaiverAcceptance ?? [],
            'confirmationContent' => $this->confirmationContent,
            'waiverContent' => $this->confirmationContent['camp_waiver'] ?? [],
        ];
    }
}
