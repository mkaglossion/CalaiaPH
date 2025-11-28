<?php

namespace App\Services;

use App\Services\BookingService;
use App\Services\InventoryService;

class PaymentService
{
    protected $bookingService;
    protected $inventoryService;

    public function __construct(BookingService $bookingService, InventoryService $inventoryService)
    {
        $this->bookingService = $bookingService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Process staycation payment
     */
    public function processStaycationPayment(array $booking, array $requestData): \Illuminate\Http\RedirectResponse
    {
        $selectedActivities = $booking['selected_activities'] ?? [];

        // Add payment info to booking
        $booking['first_name'] = $requestData['first_name'];
        $booking['last_name'] = $requestData['last_name'];
        $booking['email'] = $requestData['email'];
        $booking['phone'] = $requestData['phone'];
        $booking['special_requests'] = $requestData['special_requests'] ?? '';
        $booking['payment_method'] = $requestData['payment_method'];
        $booking['payment_date'] = now();
        $booking['transaction_id'] = 'TXN-' . strtoupper(uniqid());

        // Send confirmation email
        $this->bookingService->sendConfirmationEmail($booking, $selectedActivities);

        // Calculate total and save for confirmation
        $grandTotal = ($booking['final_totals']['accommodation_total'] ?? 0) +
                      ($booking['final_totals']['activities_total'] ?? 0);

        $this->bookingService->saveBookingForConfirmation('staycation', $booking, $selectedActivities, $grandTotal, [
            'first_name' => $requestData['first_name'],
            'last_name' => $requestData['last_name'],
            'email' => $requestData['email'],
            'phone' => $requestData['phone'],
            'special_requests' => $requestData['special_requests'] ?? '',
        ]);

        session()->forget('booking');

        return redirect()->route('booking.confirmation')
            ->with('success', 'Staycation payment successful! Booking confirmed. Check your email for details.');
    }

    /**
     * Process payment for day visits and special events
     */
    private function processBookingPayment(array $booking, array $requestData, string $activitiesSessionKey, string $confirmationType, ?string $emailClass = null): \Illuminate\Http\RedirectResponse
    {
        $selectedActivities = session($activitiesSessionKey, []);

        // Add payment info
        $booking['payment_method'] = $requestData['payment_method'];
        $booking['payment_date'] = now();
        $booking['transaction_id'] = 'TXN-' . strtoupper(uniqid());

        // Send email and calculate total
        $this->bookingService->sendConfirmationEmail($booking, $selectedActivities, $emailClass);
        $grandTotal = $this->bookingService->calculateTotal($booking, $selectedActivities);
        $booking['grand_total'] = $grandTotal;

        // Save for confirmation
        $this->bookingService->saveBookingForConfirmation($confirmationType, $booking, $selectedActivities, $grandTotal, [
            'first_name' => $requestData['first_name'],
            'last_name' => $requestData['last_name'],
            'email' => $requestData['email'],
            'phone' => $requestData['phone'],
            'special_requests' => $requestData['special_requests'] ?? '',
        ]);

        $message = $confirmationType === 'dayvisit'
            ? 'Day visit payment successful! Booking confirmed. Check your email for details.'
            : 'Special event payment successful! Booking confirmed. Check your email for details.';

        return redirect()->route('booking.confirmation')->with('success', $message);
    }

    /**
     * Process dayvisit payment
     */
    public function processDayvisitPayment(array $booking, array $requestData): \Illuminate\Http\RedirectResponse
    {
        return $this->processBookingPayment($booking, $requestData, 'dayvisit_selected_activities', 'dayvisit');
    }

    /**
     * Process special event payment
     */
    public function processSpecialEventPayment(array $booking, array $requestData): \Illuminate\Http\RedirectResponse
    {
        return $this->processBookingPayment($booking, $requestData, 'special_event_selected_activities', 'special_event', \App\Mail\SpecialEventBookingConfirmation::class);
    }

    /**
     * Process regular booking payment
     */
    public function processRegularBookingPayment(array $bookingData, array $requestData): \Illuminate\Http\RedirectResponse
    {
        $selectedActivities = session('selected_activities', []);

        // Add payment info
        $bookingData['payment_method'] = $requestData['payment_method'];
        $bookingData['payment_date'] = now();
        $bookingData['transaction_id'] = 'TXN-' . strtoupper(uniqid());

        // Send confirmation email
        $this->bookingService->sendConfirmationEmail($bookingData, $selectedActivities);

        return redirect()->route('booking.confirmation');
    }
}