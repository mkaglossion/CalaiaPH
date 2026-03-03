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

    public function createPaymentIntent($amount, $type)
    {
        // This method is deprecated - using hosted checkout instead
        throw new \Exception('Payment intents are no longer used. Use createCheckout instead.');
    }

    public function createCheckout($amount, $type, $successUrl, $failedUrl, $lineItems = null, $referenceId = null, $customerEmail = null)
    {
        Log::info('Creating PayMongo checkout session', [
            'amount' => $amount,
            'type' => $type,
            'success_url' => $successUrl,
            'failed_url' => $failedUrl,
            'reference_id' => $referenceId,
            'customer_email' => $customerEmail,
        ]);

        $client = new \GuzzleHttp\Client();

        try {
            // If detailed line items are provided, build payload using them.
            $formattedLineItems = [];
            $computedTotal = 0;

            if (is_array($lineItems) && count($lineItems) > 0) {
                foreach ($lineItems as $li) {
                    // Expect each item: ['name' => string, 'unit_price' => float, 'quantity' => int]
                    $name = $li['name'] ?? ($li['title'] ?? 'Item');
                    $unit = isset($li['unit_price']) ? (float)$li['unit_price'] : (float)($li['price'] ?? 0);
                    $qty = isset($li['quantity']) ? (int)$li['quantity'] : (int)($li['qty'] ?? 1);

                    // unit is in PHP, convert to centavos when sending to PayMongo
                    $formattedLineItems[] = [
                        'amount' => (int)round($unit * 100), // unit price in centavos
                        'currency' => 'PHP',
                        'name' => $name,
                        'quantity' => $qty,
                    ];

                    $computedTotal += $unit * $qty;
                }

                // Override top-level amount to match computed total if mismatch
                $amount = (int)round($computedTotal * 100);
            } else {
                // Fallback single generic line item (amount is total in centavos)
                $formattedLineItems[] = [
                    'amount' => (int)$amount,
                    'currency' => 'PHP',
                    'name' => ucfirst($type) . ' Booking',
                    'quantity' => 1,
                ];
            }

            $description = ucfirst($type) . ' Booking Payment';

            // Ensure there's always a reference number. Use provided one if available,
            // otherwise generate a CALAIA-XXXXXXXXXXXX reference for visibility on PayMongo.
            if (!empty($referenceId)) {
                $referenceNumber = $referenceId;
                // Append reference to description so it shows in the left panel
                $description .= ' - #' . $referenceNumber;
            } else {
                try {
                    $referenceNumber = 'CALAIA-' . strtoupper(bin2hex(random_bytes(6)));
                } catch (\Exception $e) {
                    // Fallback if random_bytes isn't available
                    $referenceNumber = 'CALAIA-' . strtoupper(substr(uniqid(), 0, 12));
                }
                // Append generated reference to description
                $description .= ' - #' . $referenceNumber;
            }

            $attributes = [
                'amount' => (int)$amount,
                'currency' => 'PHP',
                'description' => $description,
                'line_items' => $formattedLineItems,
                'payment_method_types' => ['card', 'gcash', 'paymaya', 'qrph', 'grab_pay', 'billease'],
                'success_url' => $successUrl,
                'cancel_url' => $failedUrl,
            ];

            if (!empty($customerEmail)) {
                $attributes['customer_email'] = $customerEmail;
            }
            // Attach metadata with many keys to increase chance PayMongo surfaces it
            if (!empty($referenceNumber)) {
                // Add multiple metadata keys to maximize chance PayMongo surfaces it
                $attributes['metadata'] = [
                    'reference' => $referenceNumber,
                    'reference_id' => $referenceNumber,
                    'reference_number' => $referenceNumber,
                    'reference_no' => $referenceNumber,
                    'order_reference' => $referenceNumber,
                    'order_number' => $referenceNumber,
                    'client_reference' => $referenceNumber,
                    'merchant_reference' => $referenceNumber,
                ];

                // Also add a debug-friendly flat field for PayMongo display
                $attributes['metadata']['display_reference'] = '#' . $referenceNumber;

                // Log metadata so we can inspect what was sent to PayMongo
                Log::info('PayMongo metadata prepared', $attributes['metadata']);
            }

            // Also add top-level reference fields. Some PayMongo UIs surface these
            // attributes directly (shows as "Reference Number" on the right panel).
            if (!empty($referenceNumber)) {
                $attributes['reference'] = $referenceNumber;
                $attributes['reference_number'] = $referenceNumber;
                $attributes['reference_no'] = $referenceNumber;
                $attributes['order_reference'] = $referenceNumber;
                $attributes['order_number'] = $referenceNumber;

                // Short display string also on attributes
                $attributes['display_reference'] = '#' . $referenceNumber;

                Log::info('PayMongo top-level attributes prepared', [
                    'reference' => $referenceNumber,
                    'display_reference' => '#' . $referenceNumber
                ]);
            }

            $payload = [
                'data' => [
                    'attributes' => $attributes
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
        // Calculate nights and total guests
        $nights = 0;
        if (!empty($staycationBooking['checkin']) && !empty($staycationBooking['checkout'])) {
            $start = new \DateTime($staycationBooking['checkin']);
            $end = new \DateTime($staycationBooking['checkout']);
            $diff = $start->diff($end);
            $nights = max(1, $diff->days);
        }

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
                // Food should be charged per quantity only (no nights multiplication)
                foreach ($staycationBooking['selected_foods'] as $foodData) {
                    $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1);
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
            $staycationBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'paid';
        }

        // Create booking record
        $customerName = trim((($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')));
        if (empty($customerName) && !empty($paymentData['name'])) {
            $customerName = $paymentData['name'];
        }
        if (empty(trim($customerName ?? ''))) {
            $customerName = trim(($staycationBooking['customer_info']['first_name'] ?? $staycationBooking['first_name'] ?? '') . ' ' . ($staycationBooking['customer_info']['last_name'] ?? $staycationBooking['last_name'] ?? ''));
        }
        if (empty($customerName)) {
            $customerName = 'N/A';
        }

        $bookingType = 'camping';
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
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => 'confirmed',
        ]);

        try {
            $booking->reduceInventory();
        } catch (\Exception $e) {
            Log::error("Failed to reduce inventory for booking {$booking->id}: " . $e->getMessage());
        }

        $staycations = \App\Models\Staycation::all()->keyBy('id');
        $foods = \App\Models\Food::all()->keyBy('id');

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

        session([
            'staycation_customer_info' => $paymentData,
            'staycation_selected_foods' => $staycationBooking['selected_foods'] ?? []
        ]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processDayvisitPayment($dayvisitBooking, $paymentData)
    {
        $grandTotal = $dayvisitBooking['price'] + collect(session('dayvisit_selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        $dayvisitBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $dayvisitBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $dayvisitBooking['payment_date'] = now()->toDateTimeString();

        $amount = (int) ($grandTotal * 100);

        if ($paymentData['payment_method'] === 'paymongo') {
            $dayvisitBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'paid';
        }

        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $dayvisitBooking,
            'selectedActivities' => session('dayvisit_selected_activities', []),
            'grandTotal' => $grandTotal,
            'customerInfo' => $paymentData,
            'isDayvisit' => true
        ]));

        $dayvisitBooking['selected_activities'] = session('dayvisit_selected_activities', []);
        $dayvisitBooking['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? ''),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];

        $customerName = trim((($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')));
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
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => 'confirmed',
        ]);

        session([
            'dayvisit_customer_info' => $paymentData,
            'dayvisit_grand_total' => $grandTotal
        ]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processRegularBookingPayment($bookingData, $paymentData)
    {
        $grandTotal = ($bookingData['price'] ?? 0) + collect(session('selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        $bookingData['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $bookingData['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $bookingData['payment_date'] = now()->toDateTimeString();

        $amount = (int) ($grandTotal * 100);

        if ($paymentData['payment_method'] === 'paymongo') {
            $bookingData['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
            $paymentStatus = 'paid';
        } else {
            $paymentStatus = 'paid';
        }

        Mail::to($paymentData['email'])->send(new BookingConfirmation([
            'bookingData' => $bookingData,
            'selectedActivities' => session('selected_activities', []),
            'grandTotal' => $grandTotal,
            'customerInfo' => $paymentData
        ]));

        $bookingData['selected_activities'] = session('selected_activities', []);
        $bookingData['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => ($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? ''),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];

        $customerName = trim((($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')));
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
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => 'confirmed',
        ]);

        session(['customer_info' => $paymentData, 'grand_total' => $grandTotal]);

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }
}
