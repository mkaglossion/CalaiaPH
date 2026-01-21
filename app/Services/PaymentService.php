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
        
        // Calculate nights and total guests
        $nights = 0;
        if (!empty($staycationBooking['checkin']) && !empty($staycationBooking['checkout'])) {
            $start = new \DateTime($staycationBooking['checkin']);
            $end = new \DateTime($staycationBooking['checkout']);
            $diff = $start->diff($end);
            $nights = max(1, $diff->days);
        }
        
        // Calculate total guests from accommodations (adults + children)
        $totalGuests = 0;
        foreach ($staycationBooking['accommodations'] ?? [] as $acc) {
            $totalGuests += ($acc['adults'] ?? 0) + ($acc['children'] ?? 0);
        }

        // Add payment details and calculated values
        $staycationBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $staycationBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $staycationBooking['payment_date'] = now()->toDateTimeString();
        $staycationBooking['nights'] = $nights;
        $staycationBooking['total_guests'] = $totalGuests;

        // Fetch staycation and food models for email display
        $staycations = \App\Models\Staycation::all()->keyBy('id');
        $foods = \App\Models\Food::all()->keyBy('id');

        // Send confirmation email
        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $staycationBooking,
            'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
            'selectedFoods' => $staycationBooking['selected_foods'] ?? [],
            'staycations' => $staycations,
            'foods' => $foods,
            'grandTotal' => $staycationBooking['final_totals']['grand_total'] ?? 0,
            'customerInfo' => $paymentData,
            'isStaycation' => true
        ]));

        // Store customer info and selected foods in session for confirmation page
        session([
            'staycation_customer_info' => $paymentData,
            'staycation_selected_foods' => $staycationBooking['selected_foods'] ?? []
        ]);

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