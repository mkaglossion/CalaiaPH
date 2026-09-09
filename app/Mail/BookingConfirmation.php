<?php

namespace App\Mail;

use App\Services\AdminBookingEmailRecipientService;
use App\Services\BookingConfirmationContentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $bookingData;

    /** @var array<int|string, mixed> */
    public array $selectedActivities;

    /** @var array<int|string, mixed> */
    public array $selectedActivityPackages;

    /** @var array<int|string, mixed> */
    public array $selectedFoods;

    /** @var Collection<int|string, mixed> */
    public Collection $staycations;

    /** @var Collection<int|string, mixed> */
    public Collection $foods;

    public float $grandTotal;

    /** @var array<string, mixed> */
    public array $customerInfo;

    public bool $isStaycation;
    public bool $isDayvisit;
    public bool $isSpecialEvent;

    /** @var array<string, mixed> */
    public array $confirmationContent;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->bookingData = is_array($data['bookingData'] ?? null) ? $data['bookingData'] : [];
        $this->selectedActivities = is_array($data['selectedActivities'] ?? null) ? $data['selectedActivities'] : [];
        $this->selectedActivityPackages = is_array($data['selectedActivityPackages'] ?? null) ? $data['selectedActivityPackages'] : [];
        $this->selectedFoods = is_array($data['selectedFoods'] ?? null) ? $data['selectedFoods'] : [];
        $this->staycations = collect($data['staycations'] ?? []);
        $this->foods = collect($data['foods'] ?? []);
        $this->grandTotal = (float) ($data['grandTotal'] ?? 0);
        $this->customerInfo = is_array($data['customerInfo'] ?? null) ? $data['customerInfo'] : [];
        $this->isStaycation = (bool) ($data['isStaycation'] ?? false);
        $this->isDayvisit = (bool) ($data['isDayvisit'] ?? false);
        $this->isSpecialEvent = (bool) ($data['isSpecialEvent'] ?? false);
        $this->confirmationContent = is_array($data['confirmationContent'] ?? null)
            ? $data['confirmationContent']
            : app(BookingConfirmationContentService::class)->forBookingData($this->bookingData, [
                'reference' => $this->bookingData['transaction_id'] ?? '',
                'package' => $this->bookingData['package'] ?? '',
            ]);

        if ($this->isStaycation) {
            $customerEmail = $this->customerInfo['email']
                ?? $this->bookingData['email']
                ?? data_get($this->bookingData, 'customer_info.email');
            $adminRecipients = app(AdminBookingEmailRecipientService::class)->recipients($customerEmail);

            if ($adminRecipients !== []) {
                $this->bcc($adminRecipients);
            }
        }
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
