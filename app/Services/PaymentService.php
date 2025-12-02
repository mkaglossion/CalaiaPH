<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;
use App\Mail\SpecialEventBookingConfirmation;

class PaymentService
{
    public function processStaycationPayment($staycationBooking, $paymentData)
    {
        // Process staycation payment logic here
        // For now, just send confirmation email and redirect

        // Send confirmation email
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $staycationBooking,
            'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
            'grandTotal' => $staycationBooking['final_totals']['grand_total'] ?? 0,
            'customerInfo' => $paymentData,
            'isStaycation' => true
        ]));

        // Clear session
        session()->forget(['booking', 'staycation_customer_info']);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processDayvisitPayment($dayvisitBooking, $paymentData)
    {
        // Process dayvisit payment
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $dayvisitBooking,
            'selectedActivities' => session('dayvisit_selected_activities', []),
            'grandTotal' => $dayvisitBooking['price'] + collect(session('dayvisit_selected_activities', []))->sum(function($activity) {
                return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
            }),
            'customerInfo' => $paymentData,
            'isDayvisit' => true
        ]));

        session()->forget(['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_grand_total', 'dayvisit_customer_info']);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processSpecialEventPayment($specialEventBooking, $paymentData)
    {
        // Process special event payment
        Mail::to($paymentData['email'])->send(new SpecialEventBookingConfirmation([
            'bookingData' => $specialEventBooking,
            'selectedActivities' => session('special_event_selected_activities', []),
            'grandTotal' => $specialEventBooking['price'] + collect(session('special_event_selected_activities', []))->sum(function($activity) {
                return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
            }),
            'customerInfo' => $paymentData,
            'isSpecialEvent' => true
        ]));

        session()->forget(['special_event_booking_data', 'special_event_selected_activities', 'special_event_grand_total', 'special_event_customer_info']);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processRegularBookingPayment($bookingData, $paymentData)
    {
        // Process regular booking payment
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $bookingData,
            'selectedActivities' => session('selected_activities', []),
            'grandTotal' => ($bookingData['price'] ?? 0) + collect(session('selected_activities', []))->sum(function($activity) {
                return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
            }),
            'customerInfo' => $paymentData
        ]));

        session()->forget(['booking_data', 'selected_activities']);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }
}