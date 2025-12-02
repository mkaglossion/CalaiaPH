<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

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

        // Store customer info in session for confirmation page
        session(['staycation_customer_info' => $paymentData]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processDayvisitPayment($dayvisitBooking, $paymentData)
    {
        // Calculate grand total
        $grandTotal = $dayvisitBooking['price'] + collect(session('dayvisit_selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        // Process dayvisit payment
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $dayvisitBooking,
            'selectedActivities' => session('dayvisit_selected_activities', []),
            'grandTotal' => $grandTotal,
            'customerInfo' => $paymentData,
            'isDayvisit' => true
        ]));

        // Store customer info and grand total in session for confirmation page (session will be cleared in confirmation method)
        session([
            'dayvisit_customer_info' => $paymentData,
            'dayvisit_grand_total' => $grandTotal
        ]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processSpecialEventPayment($specialEventBooking, $paymentData)
    {
        // Calculate grand total
        $grandTotal = $specialEventBooking['price'] + collect(session('special_event_selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        // Process special event payment
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $specialEventBooking,
            'selectedActivities' => session('special_event_selected_activities', []),
            'grandTotal' => $grandTotal,
            'customerInfo' => $paymentData,
            'isSpecialEvent' => true
        ]));

        // Store customer info and grand total in session for confirmation page (session will be cleared in confirmation method)
        session([
            'special_event_customer_info' => $paymentData,
            'special_event_grand_total' => $grandTotal
        ]);

        return redirect()->route('booking.specialEventConfirmation')->with('success', 'Payment processed successfully!');
    }

    public function processRegularBookingPayment($bookingData, $paymentData)
    {
        // Calculate grand total
        $grandTotal = ($bookingData['price'] ?? 0) + collect(session('selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        // Process regular booking payment
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $bookingData,
            'selectedActivities' => session('selected_activities', []),
            'grandTotal' => $grandTotal,
            'customerInfo' => $paymentData
        ]));

        // Store customer info and grand total in session for confirmation page (session will be cleared in confirmation method)
        session(['customer_info' => $paymentData, 'grand_total' => $grandTotal]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }
}