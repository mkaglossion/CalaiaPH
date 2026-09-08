<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\BookingGuest;

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

    public function createCheckout(
        int|float $amount,
        string $type,
        string $successUrl,
        string $failedUrl,
        ?array $lineItems = null,
        ?string $referenceId = null,
        ?string $customerEmail = null,
        array $metadata = []
    )
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
            $targetAmount = max(1, (int) round($amount));

            // If detailed line items are provided, build payload using them.
            $formattedLineItems = [];
            $computedTotal = 0;

            if (is_array($lineItems) && count($lineItems) > 0) {
                foreach ($lineItems as $li) {
                    // Expect each item: ['name' => string, 'unit_price' => float, 'quantity' => int]
                    $name = $li['name'] ?? ($li['title'] ?? 'Item');
                    $unit = isset($li['unit_price']) ? (float)$li['unit_price'] : (float)($li['price'] ?? 0);
                    $qty = isset($li['quantity']) ? (int)$li['quantity'] : (int)($li['qty'] ?? 1);
                    $unitAmount = (int) round($unit * 100);

                    // unit is in PHP, convert to centavos when sending to PayMongo
                    $formattedLineItems[] = [
                        'amount' => $unitAmount, // unit price in centavos
                        'currency' => 'PHP',
                        'name' => $name,
                        'quantity' => $qty,
                    ];

                    $computedTotal += $unitAmount * $qty;

                }

                if ($computedTotal !== $targetAmount) {
                    Log::warning('PayMongo line item total mismatch; using final checkout amount as a single line item.', [
                        'type' => $type,
                        'reference_id' => $referenceId,
                        'computed_total_centavos' => $computedTotal,
                        'target_amount_centavos' => $targetAmount,
                    ]);

                    $formattedLineItems = [[
                        'amount' => $targetAmount,
                        'currency' => 'PHP',
                        'name' => ucfirst($type) . ' Booking',
                        'quantity' => 1,
                    ]];
                }
            } else {
                // Fallback single generic line item (amount is total in centavos)
                $formattedLineItems[] = [
                    'amount' => $targetAmount,
                    'currency' => 'PHP',
                    'name' => ucfirst($type) . ' Booking',
                    'quantity' => 1,
                ];
            }

            $description = ucfirst($type) . ' Booking Payment';

            // Ensure amount is in centavos and integer
            $amount = $targetAmount;

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
                $metadata = collect($metadata)
                    ->filter(fn ($value) => is_scalar($value) && $value !== '')
                    ->map(fn ($value) => (string) $value)
                    ->all();

                // Add multiple metadata keys to maximize chance PayMongo surfaces it
                $attributes['metadata'] = array_merge($metadata, [
                    'reference' => $referenceNumber,
                    'reference_id' => $referenceNumber,
                    'reference_number' => $referenceNumber,
                    'reference_no' => $referenceNumber,
                    'order_reference' => $referenceNumber,
                    'order_number' => $referenceNumber,
                    'client_reference' => $referenceNumber,
                    'merchant_reference' => $referenceNumber,
                ]);

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

    public function processStaycationPayment(array $staycationBooking, array $paymentData)
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
            // If booking exists and is pending, update it to paid
            if ($existingBooking->payment_status === 'pending') {
                $this->confirmPendingBooking($existingBooking, $paymentData);
                $paymentReference = $paymentData['checkout_id'] ?? $paymentData['payment_reference'] ?? null;
                if ($paymentReference) {
                    $existingBooking->update(['payment_reference' => $paymentReference]);
                }
                return redirect()->route('booking.confirmation');
            }
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

        $staycationCategory = 'camping';
        $glampingNames = ['Standard Glamping', 'Premium Glamping', 'Standard Family Glamping Tent', 'Premium Family Glamping Tent'];
        $firstAccommodation = reset($staycationBooking['accommodations']);
        if ($firstAccommodation && isset($firstAccommodation['room_id'])) {
            $staycation = \App\Models\Staycation::find($firstAccommodation['room_id']);
            if ($staycation && in_array($staycation->name, $glampingNames)) {
                $staycationCategory = 'glamping';
            }
        }

        $staycationBooking['staycation_category'] = $staycationCategory;
        $persistedBookingData = $this->stripTransientGuestData($staycationBooking);

        $booking = DB::transaction(function () use ($staycationBooking, $paymentData, $customerName, $paymentStatus) {
            $persistedBookingData = $this->stripTransientGuestData($staycationBooking);
            $booking = Booking::create([
                'transaction_id' => $staycationBooking['transaction_id'],
                'customer_name' => $customerName,
                'customer_email' => $paymentData['email'] ?? 'N/A',
                'customer_phone' => $paymentData['phone'] ?? 'N/A',
                'booking_type' => 'staycation',
                'checkin_date' => $staycationBooking['checkin'],
                'checkout_date' => $staycationBooking['checkout'],
                'booking_data' => $persistedBookingData,
                'total_amount' => (float) ($staycationBooking['payment_summary']['grand_total_paid'] ?? ($staycationBooking['final_totals']['grand_total'] ?? 0)),
                'payment_method' => 'paymongo',
                'payment_status' => $paymentStatus,
                'status' => 'confirmed',
            ]);

            // Strict reduction: if inventory/stock cannot be reduced, rollback booking creation.
            $booking->reduceInventory(true);

            return $booking;
        });

        $this->persistBookingGuests($booking, $staycationBooking, [
            'guest_signature_payloads' => is_array($staycationBooking['guest_signature_payloads'] ?? null)
                ? $staycationBooking['guest_signature_payloads']
                : [],
            'ip_address' => $paymentData['ip_address'] ?? null,
            'user_agent' => $paymentData['user_agent'] ?? null,
            'signed_by_user_id' => $paymentData['signed_by_user_id'] ?? null,
        ]);

        $staycations = \App\Models\Staycation::all()->keyBy('id');
        $foods = \App\Models\Food::all()->keyBy('id');

        try {
            Mail::to($paymentData['email'])->queue(new BookingConfirmation([
                'bookingData' => $staycationBooking,
                'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
                'selectedActivityPackages' => $staycationBooking['selected_activity_packages'] ?? [],
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

    public function processDayvisitPayment(array $dayvisitBooking, array $paymentData)
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
            // If booking exists and is pending with checkout_id, confirm it
            if ($paymentStatus === 'paid' && $existingBooking->payment_status === 'pending') {
                $this->confirmPendingBooking($existingBooking, $paymentData);
                $paymentReference = $paymentData['checkout_id'] ?? $paymentData['payment_reference'] ?? null;
                if ($paymentReference) {
                    $existingBooking->update(['payment_reference' => $paymentReference]);
                }
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

    public function processRegularBookingPayment(array $bookingData, array $paymentData)
    {
        $selectedActivities = $bookingData['selected_activities']
            ?? ($this->canUseSession() ? session('selected_activities', []) : []);
        $grandTotal = ($bookingData['price'] ?? 0) + collect($selectedActivities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        $bookingData['transaction_id'] = $paymentData['reference_id'] ?? ('TXN-' . strtoupper(uniqid()));
        $bookingData['payment_method'] = $paymentData['payment_method'] ?? 'N/A';
        $bookingData['payment_date'] = now()->toDateTimeString();

        $amount = (int) ($grandTotal * 100);

        $bookingData['paymongo_checkout_id'] = $paymentData['checkout_id'] ?? null;
        $paymentStatus = !empty($paymentData['checkout_id']) ? 'paid' : 'pending';

        // Check if pending booking exists
        $existingBooking = Booking::where('transaction_id', $bookingData['transaction_id'])->first();
        if ($existingBooking) {
            if ($paymentStatus === 'paid' && $existingBooking->payment_status === 'pending') {
                $this->confirmPendingBooking($existingBooking, $paymentData);
            }
            return redirect()->route('booking.confirmation');
        }

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

    /**
     * Persist normalized guest rows and signature files for a booking.
     *
     * The booking payload may contain raw guest signature data while the
     * checkout is being created. This helper strips that transient payload
     * before writing booking_data and stores only the private file path.
     */
    private function persistBookingGuests(Booking $booking, array $bookingData, array $options = []): void
    {
        $guests = array_values(array_filter(
            is_array($bookingData['guests'] ?? null) ? $bookingData['guests'] : [],
            fn ($guest) => is_array($guest)
        ));

        if (empty($guests)) {
            return;
        }

        $signaturePayloads = array_values(array_filter(
            is_array($options['guest_signature_payloads'] ?? null) ? $options['guest_signature_payloads'] : [],
            fn ($payload) => is_array($payload)
        ));

        $ipAddress = $options['ip_address'] ?? null;
        $userAgent = $options['user_agent'] ?? null;
        $signedByUserId = $options['signed_by_user_id'] ?? null;

        $booking->bookingGuests()->delete();

        foreach ($guests as $index => $guest) {
            $guestType = strtolower(trim((string) ($guest['guest_type'] ?? 'adult'))) === 'child' ? 'child' : 'adult';
            $signaturePayload = $signaturePayloads[$index] ?? [];
            $signatureData = trim((string) ($signaturePayload['signature_data'] ?? $guest['signature_data'] ?? ''));
            $signaturePath = null;

            if ($signatureData !== '') {
                $signaturePath = $this->storeGuestSignature($booking, $index + 1, $signatureData);
            }

            BookingGuest::create([
                'booking_id' => $booking->id,
                'full_name' => trim((string) ($guest['full_name'] ?? '')),
                'guest_type' => $guestType,
                'birth_date' => $guest['birth_date'] ?? null,
                'gender' => $guest['gender'] ?? null,
                'contact_number' => $guest['contact_number'] ?? null,
                'emergency_contact_name' => $guest['emergency_contact_name'] ?? '',
                'emergency_contact_number' => $guest['emergency_contact_number'] ?? '',
                'emergency_contact_relationship' => $guest['emergency_contact_relationship'] ?? '',
                'waiver_acknowledged_at' => $guest['waiver_acknowledged_at'] ?? now(),
                'signed_at' => $guest['signed_at'] ?? now(),
                'signed_by_user_id' => $guest['signed_by_user_id'] ?? $signedByUserId,
                'signed_by_name' => $guest['signed_by_name'] ?? null,
                'signed_by_relationship' => $guest['signed_by_relationship'] ?? null,
                'signature_path' => $signaturePath,
                'ip_address' => $guest['ip_address'] ?? $ipAddress,
                'user_agent' => $guest['user_agent'] ?? $userAgent,
                'is_primary_contact' => (bool) ($guest['is_primary_contact'] ?? false),
            ]);
        }
    }

    private function storeGuestSignature(Booking $booking, int $guestNumber, string $signatureData): ?string
    {
        if (!str_starts_with($signatureData, 'data:image/png;base64,')) {
            return null;
        }

        $encoded = substr($signatureData, strlen('data:image/png;base64,'));
        $binary = base64_decode($encoded, true);
        $imageInfo = $binary !== false ? @getimagesizefromstring($binary) : false;
        if (
            $binary === false
            || $binary === ''
            || strlen($binary) > 1500000
            || $imageInfo === false
            || ($imageInfo['mime'] ?? null) !== 'image/png'
            || ($imageInfo[0] ?? 0) > 2400
            || ($imageInfo[1] ?? 0) > 1200
        ) {
            return null;
        }

        $path = 'signatures/bookings/' . $booking->id . '/guest-' . $guestNumber . '.png';
        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    private function stripTransientGuestData(array $bookingData): array
    {
        unset($bookingData['guest_signature_payloads']);

        return $bookingData;
    }

    /**
     * Create a pending booking record before PayMongo redirect.
     * This ensures the booking exists in DB so webhook can find it.
     */
    public function createPendingBooking(array $bookingData, array $paymentData, string $type, array $options = []): Booking
    {
        $transactionId = $paymentData['reference_id'] ?? ('TXN-' . strtoupper(uniqid()));
        $checkoutSessionId = $paymentData['checkout_session_id'] ?? null;
        $expectedAmountCentavos = (int) ($paymentData['expected_amount_centavos'] ?? 0);

        $customerName = trim((($paymentData['first_name'] ?? '') . ' ' . ($paymentData['last_name'] ?? '')));
        if (empty($customerName) && !empty($paymentData['name'])) {
            $customerName = $paymentData['name'];
        }
        if (empty($customerName)) {
            $customerName = $paymentData['email'] ?? 'N/A';
        }

        $bookingType = $type === 'staycation' ? 'staycation' : 'dayvisit';

        $checkinDate = $bookingData['checkin'] ?? ($bookingData['booking_date'] ?? now()->toDateString());
        $checkoutDate = $bookingData['checkout'] ?? null;
        $bookingPayload = $this->withPaymentAttemptMetadata(
            array_merge($this->stripTransientGuestData($bookingData), [
                'payment_data' => $paymentData,
            ]),
            'pending',
            'checkout_created'
        );

        $booking = Booking::create([
            'transaction_id' => $transactionId,
            'customer_name' => $customerName,
            'customer_email' => $paymentData['email'] ?? 'N/A',
            'customer_phone' => $paymentData['phone'] ?? 'N/A',
            'booking_type' => $bookingType,
            'checkin_date' => $checkinDate,
            'checkout_date' => $checkoutDate,
            'booking_data' => $bookingPayload,
            'total_amount' => $expectedAmountCentavos > 0 ? ($expectedAmountCentavos / 100) : 0,
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $this->persistBookingGuests($booking, $bookingData, $options);

        Log::info('Created pending booking before PayMongo redirect', [
            'booking_id' => $booking->id,
            'transaction_id' => $transactionId,
            'checkout_session_id' => $checkoutSessionId,
            'type' => $type,
        ]);

        return $booking;
    }

    private function queueConfirmedStaycationEmail(Booking $booking, array $paymentData): void
    {
        $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
        $accommodations = collect($bookingData['accommodations'] ?? [])
            ->filter(fn ($accommodation) => is_array($accommodation));

        $bookingData['transaction_id'] = $booking->transaction_id;
        $bookingData['total_guests'] = $accommodations->sum(
            fn ($accommodation) => (int) ($accommodation['adults'] ?? 0) + (int) ($accommodation['children'] ?? 0)
        );
        $bookingData['adults_total'] = $accommodations->sum(fn ($accommodation) => (int) ($accommodation['adults'] ?? 0));
        $bookingData['children_total'] = $accommodations->sum(fn ($accommodation) => (int) ($accommodation['children'] ?? 0));
        $bookingData['payment_summary']['grand_total_paid'] = (float) $booking->total_amount;

        try {
            Mail::to($booking->customer_email)->queue(new BookingConfirmation([
                'bookingData' => $bookingData,
                'selectedActivities' => $bookingData['selected_activities'] ?? [],
                'selectedActivityPackages' => $bookingData['selected_activity_packages'] ?? [],
                'selectedFoods' => $bookingData['selected_foods'] ?? [],
                'staycations' => \App\Models\Staycation::all()->keyBy('id'),
                'foods' => \App\Models\Food::all()->keyBy('id'),
                'grandTotal' => (float) $booking->total_amount,
                'customerInfo' => array_merge([
                    'email' => $booking->customer_email,
                    'phone' => $booking->customer_phone,
                ], $paymentData),
                'isStaycation' => true,
            ]));
        } catch (\Throwable $e) {
            Log::error('Failed to send confirmed staycation booking email', [
                'booking_transaction' => $booking->transaction_id,
                'email' => $booking->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update pending booking to paid status after successful payment.
     */
    public function confirmPendingBooking(Booking $booking, array $paymentData): Booking
    {
        $paidAmountCentavos = isset($paymentData['paid_amount_centavos'])
            ? (int) $paymentData['paid_amount_centavos']
            : null;
        $expectedAmountCentavos = (int) ($paymentData['expected_amount_centavos'] ?? 0);
        $chargedAmountCentavos = $paidAmountCentavos ?? ($expectedAmountCentavos > 0 ? $expectedAmountCentavos : null);
        $grandTotalPaid = $chargedAmountCentavos !== null
            ? ((float) $chargedAmountCentavos / 100)
            : $booking->total_amount;

        $checkoutSessionId = $paymentData['checkout_id'] ?? $paymentData['checkout_session_id'] ?? null;
        $paymentReference = $paymentData['payment_reference'] ?? $paymentData['reference_id'] ?? null;

        if (($booking->payment_status ?? null) === 'paid') {
            $updates = array_filter([
                'paymongo_checkout_id' => $checkoutSessionId,
                'payment_reference' => $paymentReference,
            ], fn ($value) => !empty($value));

            if (!empty($updates)) {
                $booking->update($updates);
            }

            Log::info('Skipped duplicate booking confirmation; booking already paid', [
                'booking_id' => $booking->id,
                'transaction_id' => $booking->transaction_id,
                'checkout_session_id' => $checkoutSessionId,
            ]);

            return $booking->fresh();
        }

        DB::transaction(function () use ($booking, $grandTotalPaid, $checkoutSessionId, $paymentReference, $paymentData) {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if (($lockedBooking->payment_status ?? null) === 'paid') {
                return;
            }

            $lockedBooking->update([
                'total_amount' => $grandTotalPaid,
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'paymongo_checkout_id' => $checkoutSessionId,
                'payment_reference' => $paymentReference,
            ]);

            // Handle inventory based on booking type
            $bookingData = $lockedBooking->booking_data ?? [];
            $bookingType = $lockedBooking->booking_type ?? ($bookingData['booking_type'] ?? null);

            if ($bookingType === 'dayvisit') {
                // For dayvisit, book the slot via DayTourInventoryService
                $packageId = $bookingData['package_id'] ?? null;
                $date = $lockedBooking->checkin_date;
                if ($packageId && $date) {
                    try {
                        $dayTourInventoryService = app(\App\Services\DayTourInventoryService::class);
                        $dayTourInventoryService->bookSlot($packageId, $date->toDateString(), $checkoutSessionId);
                        Log::info('Day visit inventory slot booked via webhook', [
                            'booking_id' => $lockedBooking->id,
                            'package_id' => $packageId,
                            'date' => $date->toDateString(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to book day visit inventory slot via webhook', [
                            'booking_id' => $lockedBooking->id,
                            'package_id' => $packageId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } else {
                // For staycation, reduce inventory
                $lockedBooking->reduceInventory(true);
            }
        });

        $booking->refresh();
        if ($booking->bookingGuests()->count() === 0) {
            $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
            $this->persistBookingGuests($booking, $bookingData, [
                'guest_signature_payloads' => is_array($bookingData['guest_signature_payloads'] ?? null)
                    ? $bookingData['guest_signature_payloads']
                    : [],
            ]);
        }

        if (($booking->booking_type ?? null) === 'staycation') {
            $this->queueConfirmedStaycationEmail($booking, $paymentData);
        }

        Log::info('Confirmed pending booking after successful payment', [
            'booking_id' => $booking->id,
            'transaction_id' => $booking->transaction_id,
            'checkout_session_id' => $checkoutSessionId,
            'type' => $booking->booking_type,
        ]);

        return $booking;
    }

    /**
     * Read the payment attached to a PayMongo checkout session before expiring it.
     */
    public function getCheckoutPaymentStatus(?string $checkoutSessionId): array
    {
        if (empty($checkoutSessionId) || empty(config('services.paymongo.secret_key'))) {
            return ['status' => 'unknown'];
        }

        try {
            $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                ->timeout(10)
                ->get('https://api.paymongo.com/v1/checkout_sessions/' . $checkoutSessionId);

            if (!$response->successful()) {
                return ['status' => 'unknown'];
            }

            $attributes = $response->json('data.attributes', []);
            $payments = $attributes['payments'] ?? [];
            $payment = is_array($payments) && !empty($payments) ? reset($payments) : [];
            $paymentAttributes = is_array($payment['attributes'] ?? null) ? $payment['attributes'] : [];

            if (($paymentAttributes['status'] ?? null) === 'paid' || !empty($attributes['paid_at'])) {
                return [
                    'status' => 'paid',
                    'payment_reference' => $payment['id'] ?? null,
                    'paid_amount_centavos' => isset($paymentAttributes['amount']) ? (int) $paymentAttributes['amount'] : null,
                ];
            }

            return ['status' => 'not_paid'];
        } catch (\Throwable $e) {
            Log::warning('Unable to verify PayMongo checkout before expiry', [
                'checkout_session_id' => $checkoutSessionId,
                'error' => $e->getMessage(),
            ]);

            return ['status' => 'unknown'];
        }
    }

    /**
     * Mark pending booking as failed.
     */
    public function failPendingBooking(
        Booking $booking,
        ?string $checkoutSessionId = null,
        string $attemptState = 'failed',
        ?string $reason = null
    ): void
    {
        $bookingData = $this->withPaymentAttemptMetadata(
            is_array($booking->booking_data) ? $booking->booking_data : [],
            $attemptState,
            $reason ?? $attemptState
        );

        $booking->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
            'booking_data' => $bookingData,
        ]);

        if ($checkoutSessionId) {
            $booking->update(['paymongo_checkout_id' => $checkoutSessionId]);
        }

        Log::warning('Marked pending booking as failed', [
            'booking_id' => $booking->id,
            'transaction_id' => $booking->transaction_id,
            'checkout_session_id' => $checkoutSessionId,
            'attempt_state' => $attemptState,
            'reason' => $reason,
        ]);
    }

    /**
     * Resolve a pending PayMongo booking by checkout session or cached reference.
     */
    public function resolvePendingBookingForCheckout(?string $checkoutSessionId, ?string $referenceId = null): ?Booking
    {
        if (empty($referenceId) && !empty($checkoutSessionId)) {
            $referenceId = Cache::get('paymongo_checkout_reference:' . $checkoutSessionId);
        }

        $booking = null;

        if (!empty($referenceId)) {
            $booking = Booking::where('transaction_id', $referenceId)
                ->where('payment_status', 'pending')
                ->latest('id')
                ->first();
        }

        if (!$booking && !empty($checkoutSessionId)) {
            $booking = Booking::where('paymongo_checkout_id', $checkoutSessionId)
                ->where('payment_status', 'pending')
                ->latest('id')
                ->first();
        }

        return $booking;
    }

    /**
     * Mark a pending PayMongo booking as failed and clear its recovery cache.
     */
    public function failPendingBookingForCheckout(
        ?string $checkoutSessionId,
        ?string $referenceId = null,
        string $attemptState = 'failed',
        ?string $reason = null
    ): ?Booking
    {
        $booking = $this->resolvePendingBookingForCheckout($checkoutSessionId, $referenceId);

        if (!$booking) {
            Log::info('No pending booking found for failed checkout cleanup', [
                'checkout_session_id' => $checkoutSessionId,
                'reference_id' => $referenceId,
            ]);

            return null;
        }

        $resolvedReferenceId = $referenceId ?: $booking->transaction_id;

        $this->failPendingBooking($booking, $checkoutSessionId, $attemptState, $reason);

        if (!empty($resolvedReferenceId)) {
            Cache::forget('paymongo_checkout_context:' . $resolvedReferenceId);
        }

        if (!empty($checkoutSessionId)) {
            Cache::forget('paymongo_checkout_reference:' . $checkoutSessionId);
        }

        return $booking->fresh();
    }

    private function withPaymentAttemptMetadata(array $bookingData, string $state, string $reason): array
    {
        $attempt = is_array($bookingData['payment_attempt'] ?? null)
            ? $bookingData['payment_attempt']
            : [];

        $openedAt = $attempt['opened_at'] ?? now()->toDateTimeString();

        $bookingData['payment_attempt'] = array_merge($attempt, [
            'record_type' => 'payment_attempt',
            'state' => $state,
            'reason' => $reason,
            'opened_at' => $openedAt,
            'updated_at' => now()->toDateTimeString(),
        ]);

        if (in_array($state, ['failed', 'expired'], true)) {
            $bookingData['payment_attempt']['closed_at'] = now()->toDateTimeString();
        }

        return $bookingData;
    }
}
