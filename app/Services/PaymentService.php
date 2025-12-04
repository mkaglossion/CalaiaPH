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

        // Add payment details
        $staycationBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $staycationBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $staycationBooking['payment_date'] = now()->toDateTimeString();

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

        // Add payment details
        $dayvisitBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $dayvisitBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $dayvisitBooking['payment_date'] = now()->toDateTimeString();

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


    public function processRegularBookingPayment($bookingData, $paymentData)
    {
        // Calculate grand total
        $grandTotal = ($bookingData['price'] ?? 0) + collect(session('selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        // Add payment details
        $bookingData['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $bookingData['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $bookingData['payment_date'] = now()->toDateTimeString();

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