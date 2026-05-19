<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

            // Ensure amount is in centavos and integer
            $amount = (int) round($amount);

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
                'payment_method_types' => ['card', 'gcash', 'paymaya', 'qrph', 'grab_pay'],
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
        $staycationBooking['transaction_id'] = $paymentData['reference_id'] ?? ('TXN-' . strtoupper(uniqid()));
        $staycationBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $staycationBooking['payment_date'] = now()->toDateTimeString();
        $staycationBooking['nights'] = $nights;
        $staycationBooking['total_guests'] = $totalGuests;
        $staycationBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
        $staycationBooking['customer_info'] = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name'  => $paymentData['last_name'] ?? '',
            'name'       => trim(($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')),
            'email'      => $paymentData['email'] ?? '',
            'phone'      => $paymentData['phone'] ?? '',
        ];

        // Build canonical totals so UI/email/admin all read the same source of truth.
        $accommodationTotal = 0.0;
        foreach ($staycationBooking['accommodations'] ?? [] as $acc) {
            $accommodationTotal += (float) ($acc['total_price'] ?? 0);
        }

        $foodTotal = 0.0;
        foreach ($staycationBooking['selected_foods'] ?? [] as $foodData) {
            $foodTotal += (float) ($foodData['price'] ?? 0) * (int) ($foodData['quantity'] ?? 1);
        }

        $activitiesTotal = 0.0;
        foreach ($staycationBooking['selected_activities'] ?? [] as $activity) {
            $activitiesTotal += (float) ($activity['price'] ?? 0) * (int) ($activity['guests'] ?? 1);
        }

        $subtotalBeforeDiscount = max(0.0, $accommodationTotal + $foodTotal + $activitiesTotal);
        $promoDiscount = max(0.0, (float) ($paymentData['promo_discount'] ?? 0));
        $subtotalAfterDiscount = max(0.0, $subtotalBeforeDiscount - $promoDiscount);

        $vatService = app(\App\Services\VatService::class);
        $vatBreakdown = $vatService->generateBreakdown((float) $subtotalAfterDiscount, false); // VAT exclusive -> add VAT
        $computedVatAmount = (float) ($vatBreakdown['vat_amount'] ?? 0);
        $computedGrossTotal = (float) ($vatBreakdown['gross_amount'] ?? $subtotalAfterDiscount);
        $currentVatRate = (float) ($vatBreakdown['vat_rate'] ?? $vatService->getCurrentVatRate());

        $expectedAmountCentavos = (int) ($paymentData['expected_amount_centavos'] ?? 0);
        $paidAmountCentavos = isset($paymentData['paid_amount_centavos'])
            ? (int) $paymentData['paid_amount_centavos']
            : null;
        $chargedAmountCentavos = $paidAmountCentavos ?? ($expectedAmountCentavos > 0 ? $expectedAmountCentavos : null);
        $grandTotalPaid = $chargedAmountCentavos !== null
            ? ((float) $chargedAmountCentavos / 100)
            : $computedGrossTotal;

        $staycationBooking['payment_summary'] = [
            'subtotal_before_discount' => $subtotalBeforeDiscount,
            'promo_discount' => $promoDiscount,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'vat_rate' => $currentVatRate,
            'vat_amount' => $computedVatAmount,
            'grand_total_paid' => $grandTotalPaid,
            'expected_amount_centavos' => $expectedAmountCentavos,
            'paid_amount_centavos' => $paidAmountCentavos,
            'checkout_id' => $paymentData['checkout_id'] ?? null,
        ];

        $staycationBooking['final_totals'] = [
            'accommodation_total' => $accommodationTotal,
            'food_total' => $foodTotal,
            'activities_total' => $activitiesTotal,
            'subtotal_before_discount' => $subtotalBeforeDiscount,
            'promo_discount' => $promoDiscount,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'vat_amount' => $computedVatAmount,
            'grand_total' => $grandTotalPaid,
        ];

        $paymentStatus = !empty($paymentData['checkout_id']) ? 'paid' : 'pending';

        $existingBooking = Booking::where('transaction_id', $staycationBooking['transaction_id'])->first();
        if ($existingBooking) {
            Log::warning('Duplicate staycation payment processing prevented', [
                'transaction_id' => $staycationBooking['transaction_id'],
                'booking_id' => $existingBooking->id,
            ]);
            return redirect()->route('booking.confirmation');
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

        $booking = DB::transaction(function () use ($staycationBooking, $paymentData, $bookingType, $customerName, $paymentStatus) {
            $booking = Booking::create([
                'transaction_id' => $staycationBooking['transaction_id'],
                'customer_name' => $customerName,
                'customer_email' => $paymentData['email'] ?? 'N/A',
                'customer_phone' => $paymentData['phone'] ?? 'N/A',
                'booking_type' => $bookingType,
                'checkin_date' => $staycationBooking['checkin'],
                'checkout_date' => $staycationBooking['checkout'],
                'booking_data' => $staycationBooking,
                'total_amount' => (float) ($staycationBooking['payment_summary']['grand_total_paid'] ?? ($staycationBooking['final_totals']['grand_total'] ?? 0)),
                'payment_method' => 'paymongo',
                'payment_status' => $paymentStatus,
                'status' => 'confirmed',
            ]);

            // Strict reduction: if inventory/stock cannot be reduced, rollback booking creation.
            $booking->reduceInventory(true);

            return $booking;
        });

        $staycations = \App\Models\Staycation::all()->keyBy('id');
        $foods = \App\Models\Food::all()->keyBy('id');

        try {
            Mail::to($paymentData['email'])->queue(new BookingConfirmation([
                'bookingData' => $staycationBooking,
                'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
                'selectedFoods' => $staycationBooking['selected_foods'] ?? [],
                'staycations' => $staycations,
                'foods' => $foods,
                'grandTotal' => (float) ($staycationBooking['payment_summary']['grand_total_paid'] ?? ($staycationBooking['final_totals']['grand_total'] ?? 0)),
                'customerInfo' => $paymentData,
                'isStaycation' => true
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send staycation booking confirmation email', [
                'booking_transaction' => $staycationBooking['transaction_id'],
                'email' => $paymentData['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->canUseSession()) {
            session([
                'booking' => $staycationBooking,
                'staycation_customer_info' => $paymentData,
                'staycation_selected_foods' => $staycationBooking['selected_foods'] ?? []
            ]);
        }

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processDayvisitPayment($dayvisitBooking, $paymentData)
    {
        $selectedActivities = $dayvisitBooking['selected_activities']
            ?? ($this->canUseSession() ? session('dayvisit_selected_activities', []) : []);

        $grandTotal = ($dayvisitBooking['price'] ?? 0) + collect($selectedActivities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        $dayvisitBooking['transaction_id'] = $paymentData['reference_id']
            ?? ($dayvisitBooking['transaction_id'] ?? ('TXN-' . strtoupper(uniqid())));
        $dayvisitBooking['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $dayvisitBooking['payment_date'] = now()->toDateTimeString();

        $dayvisitBooking['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
        $paymentStatus = !empty($paymentData['checkout_id']) ? 'paid' : 'pending';
        $expectedAmountCentavos = (int) ($paymentData['expected_amount_centavos'] ?? 0);
        $paidAmountCentavos = isset($paymentData['paid_amount_centavos'])
            ? (int) $paymentData['paid_amount_centavos']
            : null;
        $chargedAmountCentavos = $paidAmountCentavos ?? ($expectedAmountCentavos > 0 ? $expectedAmountCentavos : null);
        $grandTotalPaid = $chargedAmountCentavos !== null
            ? ((float) $chargedAmountCentavos / 100)
            : (float) $grandTotal;

        $dayvisitBooking['payment_summary'] = [
            'subtotal_before_discount' => (float) $grandTotal,
            'promo_discount' => 0.0,
            'subtotal_after_discount' => (float) $grandTotal,
            'vat_amount' => 0.0,
            'grand_total_paid' => (float) $grandTotalPaid,
            'expected_amount_centavos' => $expectedAmountCentavos,
            'paid_amount_centavos' => $paidAmountCentavos,
            'checkout_id' => $paymentData['checkout_id'] ?? null,
        ];

        $existingBooking = Booking::where('transaction_id', $dayvisitBooking['transaction_id'])->first();
        if ($existingBooking) {
            if ($paymentStatus === 'paid' && ($existingBooking->payment_status ?? 'pending') !== 'paid') {
                $existingBooking->payment_status = 'paid';
                $existingBooking->payment_method = 'paymongo';
                $existingBooking->save();
            }
            return redirect()->route('booking.confirmation');
        }

        try {
            Mail::to($paymentData['email'])->queue(new BookingConfirmation([
                'bookingData' => $dayvisitBooking,
                'selectedActivities' => $selectedActivities,
                'grandTotal' => $grandTotalPaid,
                'customerInfo' => $paymentData,
                'isDayvisit' => true
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send dayvisit booking confirmation email', [
                'booking_transaction' => $dayvisitBooking['transaction_id'],
                'email' => $paymentData['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $dayvisitBooking['selected_activities'] = $selectedActivities;
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
            'total_amount' => $grandTotalPaid,
            'payment_method' => 'paymongo',
            'payment_status' => $paymentStatus,
            'status' => 'confirmed',
        ]);

        if ($this->canUseSession()) {
            session([
                'dayvisit_customer_info' => $paymentData,
                'dayvisit_grand_total' => $grandTotalPaid
            ]);
        }

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    public function processRegularBookingPayment($bookingData, $paymentData)
    {
        $selectedActivities = $bookingData['selected_activities']
            ?? ($this->canUseSession() ? session('selected_activities', []) : []);
        $grandTotal = ($bookingData['price'] ?? 0) + collect($selectedActivities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        $bookingData['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $bookingData['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $bookingData['payment_date'] = now()->toDateTimeString();

        $amount = (int) ($grandTotal * 100);

        $bookingData['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
        $paymentStatus = !empty($paymentData['checkout_id']) ? 'paid' : 'pending';

        try {
            Mail::to($paymentData['email'])->queue(new BookingConfirmation([
                'bookingData' => $bookingData,
                'selectedActivities' => $selectedActivities,
                'grandTotal' => $grandTotal,
                'customerInfo' => $paymentData
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send regular booking confirmation email', [
                'booking_transaction' => $bookingData['transaction_id'],
                'email' => $paymentData['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $bookingData['selected_activities'] = $selectedActivities;
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

        if ($this->canUseSession()) {
            session(['customer_info' => $paymentData, 'grand_total' => $grandTotal]);
        }

        return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
    }

    private function canUseSession(): bool
    {
        if (!app()->bound('request')) {
            return false;
        }

        try {
            return request()->hasSession();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
