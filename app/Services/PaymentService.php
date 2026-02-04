<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmation;
use App\Models\Booking;

class PaymentService
{
    public function __construct()
    {
        // PayMongo client not needed for direct API calls
    }

    /**
     * Categorize line item based on its name
     */
    private function categorizeLineItem($name)
    {
        $nameLower = strtolower($name);
        
        // Check if accommodation
        if (strpos($nameLower, 'accommodation') !== false) {
            return 'accommodation';
        }
        
        // Check if food
        if (strpos($nameLower, 'messhall') !== false || 
            strpos($nameLower, 'cook') !== false || 
            strpos($nameLower, 'special') !== false) {
            return 'food';
        }
        
        // Default to activity
        return 'activity';
    }

    /**
     * Prepend category prefix to item name for PayMongo sorting
     */
    private function prependCategoryPrefix($name, $category)
    {
        // Return name without prefix - sorting is handled server-side
        return $name;
    }

    public function createPaymentIntent($amount, $type)
    {
        // This method is deprecated - using hosted checkout instead
        throw new \Exception('Payment intents are no longer used. Use createCheckout instead.');
    }

    public function createCheckout($amount, $type, $successUrl, $failedUrl, $lineItems = null)
    {
        Log::info('Creating PayMongo checkout session', [
            'amount' => $amount,
            'type' => $type,
            'success_url' => $successUrl,
            'failed_url' => $failedUrl
        ]);

        $client = new \GuzzleHttp\Client();

        try {
            // Use provided line items or create a generic one
            $items = $lineItems ?? [
                [
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'name' => ucfirst($type) . ' Booking',
                    'quantity' => 1,
                ]
            ];

            // Line items are already sorted in the correct order from the controller
            // Accommodation -> Food -> Activities
            // Add numeric prefix to item names to force PayMongo to display in correct order
            if ($lineItems) {
                foreach ($items as $idx => $item) {
                    $category = $this->categorizeLineItem($item['name']);
                    $items[$idx]['name'] = $this->prependCategoryPrefix($item['name'], $category);
                }
            }

            $payload = [
                'data' => [
                    'attributes' => [
                        'amount' => $amount,
                        'currency' => 'PHP',
                        'description' => ucfirst($type) . ' Booking Payment',
                        'line_items' => $items,
                        'payment_method_types' => ['card', 'gcash', 'paymaya', 'qrph', 'grab_pay', 'billease'],
                        'success_url' => $successUrl,
                        'cancel_url' => $failedUrl,
                    ]
                ]
            ];

            Log::info('PayMongo API payload:', $payload);

            $response = $client->post('https://api.paymongo.com/v1/checkout_sessions', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info('PayMongo API response:', $data);
            Log::info('Available payment methods:', $data['data']['attributes']['payment_method_types'] ?? 'not set');

            return (object) [
                'checkout_url' => $data['data']['attributes']['checkout_url'],
                'id' => $data['data']['id']
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('PayMongo API Request Error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('PayMongo API Response: ' . $responseBody);
            }
            throw new \Exception('PayMongo API request failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('PayMongo checkout creation failed: ' . $e->getMessage());
            throw new \Exception('PayMongo checkout creation failed: ' . $e->getMessage());
        }
    }

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
                    $activitiesTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                }
            }

            $staycationBooking['final_totals'] = [
                'accommodation_total' => $accommodationTotal,
                'food_total' => $foodTotal,
                'activities_total' => $activitiesTotal,
                'grand_total' => $accommodationTotal + $foodTotal + $activitiesTotal
            ];
        }

        // Process payment with PayMongo
        $amount = (int) (($staycationBooking['final_totals']['grand_total'] ?? 0) * 100); // Convert to centavos

        if ($paymentData['payment_method'] === 'paymongo') {
            // For hosted checkout, payment success is already verified in the controller
            // We just need to mark it as paid
            $staycationBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            // For other payment methods, assume paid
            $paymentStatus = 'paid';
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
            'payment_method' => 'paymongo', // Update to PayMongo
            'payment_status' => $paymentStatus,
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

        // Process payment with PayMongo
        $amount = (int) ($grandTotal * 100); // Convert to centavos

        if ($paymentData['payment_method'] === 'paymongo') {
            // For hosted checkout, payment success is already verified
            // We just need to mark it as paid
            $dayvisitBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'paid';
        }

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
            'payment_method' => 'paymongo', // Update to PayMongo
            'payment_status' => $paymentStatus,
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

        // Process payment with PayMongo
        $amount = (int) ($grandTotal * 100); // Convert to centavos

        if ($paymentData['payment_method'] === 'paymongo') {
            // For hosted checkout, payment success is already verified
            // We just need to mark it as paid
            $bookingData['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'paid';
        }

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
            'payment_method' => 'paymongo', // Update to PayMongo
            'payment_status' => $paymentStatus,
            'status' => 'confirmed',
        ]);

        // Store customer info and grand total in session for confirmation page (session will be cleared in confirmation method)
        session(['customer_info' => $paymentData, 'grand_total' => $grandTotal]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }
}