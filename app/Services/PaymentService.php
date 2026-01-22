<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmation;
use App\Models\Booking;

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
        // Persist customer info alongside booking data so admin views can show name
        $staycationBooking['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => trim(($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];

        // Calculate final totals if not already set
        if (!isset($staycationBooking['final_totals'])) {
            $accommodationTotal = 0;
            foreach ($staycationBooking['accommodations'] ?? [] as $acc) {
                $accommodationTotal += $acc['total_price'] ?? 0;
            }

            $foodTotal = 0;
            if (!empty($staycationBooking['selected_foods'])) {
                foreach ($staycationBooking['selected_foods'] as $foodData) {
                    $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1) * $nights;
                }
            }

            $activitiesTotal = 0;
            if (!empty($staycationBooking['selected_activities'])) {
                foreach ($staycationBooking['selected_activities'] as $activity) {
                    $activitiesTotal += 500 * ($activity['guests'] ?? 1);
                }
            }

            $staycationBooking['final_totals'] = [
                'accommodation_total' => $accommodationTotal,
                'food_total' => $foodTotal,
                'activities_total' => $activitiesTotal,
                'grand_total' => $accommodationTotal + $foodTotal + $activitiesTotal
            ];
        }

        // Create booking record in database
        $customerName = trim(
            ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')
        );
        if (empty($customerName) && !empty($paymentData['name'])) {
            $customerName = $paymentData['name'];
        }
        // Fallback to booking data first/last if payment payload lacks names (legacy flows)
        if (empty(trim($customerName ?? ''))) {
            $customerName = trim(($staycationBooking['customer_info']['first_name'] ?? $staycationBooking['first_name'] ?? '') . ' ' . ($staycationBooking['customer_info']['last_name'] ?? $staycationBooking['last_name'] ?? ''));
        }
        if (empty($customerName)) {
            $customerName = 'N/A';
        }

        // Determine booking type (glamping or camping) based on first accommodation
        $bookingType = 'camping'; // default
        $glampingNames = ['Standard Glamping', 'Premium Glamping', 'Standard Family Glamping Tent', 'Premium Family Glamping Tent'];
        $firstAccommodation = reset($staycationBooking['accommodations']);
        if ($firstAccommodation && isset($firstAccommodation['room_id'])) {
            $staycation = \App\Models\Staycation::find($firstAccommodation['room_id']);
            if ($staycation && in_array($staycation->name, $glampingNames)) {
                $bookingType = 'glamping';
            }
        }

        $booking = Booking::create([
            'transaction_id' => $staycationBooking['transaction_id'],
            'customer_name' => $customerName,
            'customer_email' => $paymentData['email'] ?? 'N/A',
            'customer_phone' => $paymentData['phone'] ?? 'N/A',
            'booking_type' => $bookingType,
            'checkin_date' => $staycationBooking['checkin'],
            'checkout_date' => $staycationBooking['checkout'],
            'booking_data' => $staycationBooking,
            'total_amount' => $staycationBooking['final_totals']['grand_total'] ?? 0,
            'payment_method' => $paymentData['payment_method'] ?? 'N/A',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        // Reduce inventory for all accommodations
        try {
            $booking->reduceInventory();
        } catch (\Exception $e) {
            Log::error("Failed to reduce inventory for booking {$booking->id}: " . $e->getMessage());
            // Continue with the booking even if inventory reduction fails
        }

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

        // Persist booking for user history
        $dayvisitBooking['selected_activities'] = session('dayvisit_selected_activities', []);
        $dayvisitBooking['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? ''),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];
        $customerName = trim(
            ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')
        );
        if (empty($customerName) && !empty($paymentData['name'])) {
            $customerName = $paymentData['name'];
        }
        if (empty(trim($customerName ?? ''))) {
            $customerName = trim(($dayvisitBooking['customer_info']['first_name'] ?? $dayvisitBooking['first_name'] ?? '') . ' ' . ($dayvisitBooking['customer_info']['last_name'] ?? $dayvisitBooking['last_name'] ?? ''));
        }
        if (empty($customerName)) {
            $customerName = $paymentData['email'] ?? 'N/A';
        }
        Booking::create([
            'transaction_id' => $dayvisitBooking['transaction_id'],
            'customer_name' => !empty($customerName) ? $customerName : 'N/A',
            'customer_email' => $paymentData['email'] ?? 'N/A',
            'customer_phone' => $paymentData['phone'] ?? 'N/A',
            'booking_type' => 'dayvisit',
            'checkin_date' => $dayvisitBooking['booking_date'] ?? null,
            'checkout_date' => null,
            'booking_data' => $dayvisitBooking,
            'total_amount' => $grandTotal,
            'payment_method' => $paymentData['payment_method'] ?? 'N/A',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

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

        // Persist booking for user history (legacy/regular flow)
        $bookingData['selected_activities'] = session('selected_activities', []);
        $bookingData['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? ''),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];
        $customerName = trim(
            ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')
        );
        if (empty($customerName) && !empty($paymentData['name'])) {
            $customerName = $paymentData['name'];
        }
        if (empty(trim($customerName ?? ''))) {
            $customerName = trim(($bookingData['customer_info']['first_name'] ?? $bookingData['first_name'] ?? '') . ' ' . ($bookingData['customer_info']['last_name'] ?? $bookingData['last_name'] ?? ''));
        }
        if (empty($customerName)) {
            $customerName = $paymentData['email'] ?? 'N/A';
        }
        Booking::create([
            'transaction_id' => $bookingData['transaction_id'],
            'customer_name' => !empty($customerName) ? $customerName : 'N/A',
            'customer_email' => $paymentData['email'] ?? 'N/A',
            'customer_phone' => $paymentData['phone'] ?? 'N/A',
            'booking_type' => $bookingData['booking_type'] ?? 'regular',
            'checkin_date' => $bookingData['booking_date'] ?? now()->toDateString(),
            'checkout_date' => null,
            'booking_data' => $bookingData,
            'total_amount' => $grandTotal,
            'payment_method' => $paymentData['payment_method'] ?? 'N/A',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        // Store customer info and grand total in session for confirmation page (session will be cleared in confirmation method)
        session(['customer_info' => $paymentData, 'grand_total' => $grandTotal]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }
}