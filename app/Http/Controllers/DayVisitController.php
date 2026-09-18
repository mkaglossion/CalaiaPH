<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\ValidationService;
use App\Services\InventoryService;
use App\Services\DayTourInventoryService;
use App\Services\VatService;
use App\Models\DayTour;
use App\Models\Booking;
use Carbon\Carbon;

class DayVisitController extends Controller
{
    protected $bookingService;
    protected $paymentService;
    protected $inventoryService;
    protected $dayTourInventoryService;
    protected $vatService;

    public function __construct(
        BookingService $bookingService, 
        PaymentService $paymentService, 
        InventoryService $inventoryService,
        DayTourInventoryService $dayTourInventoryService,
        VatService $vatService
    )
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
        $this->dayTourInventoryService = $dayTourInventoryService;
        $this->vatService = $vatService;
    }

    /**
     * Show day visit booking index (redirect to availability)
     */
    public function index()
    {
        return redirect()->route('dayvisit.availability');
    }

    /**
     * Show day visit availability page
     */
    public function availability(Request $request)
    {
        if ($request->isMethod('POST')) {
            // Handle POST request from check-availability component
            $date = $request->input('check_in');
            $guests = $request->input('guests', 1);
            
            // Store selected date in session
            session(['selected_dayvisit_date' => $date]);
            
            // Redirect to availability page with parameters
            return redirect()->route('dayvisit.availability', ['date' => $date, 'guests' => $guests]);
        }
        
        // Handle GET request to show availability page
        return view('booking.dayvisit-availability');
    }

    /**
     * Helper function to format price without .00 for whole numbers
     */
    private function formatPrice($price)
    {
        $num = (float) $price;
        if (fmod($num, 1) == 0) {
            return '₱' . number_format($num);
        }
        return '₱' . number_format($num, 2);
    }

    /**
     * Get day visit availability API
     */
    public function getAvailabilityApi(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        
        // Get all active packages with their inventory for the selected date
        $packages = DayTour::where('is_active', true)->get();
        
        $packagesData = [];

        if ($packages->count() > 0) {
            $packagesData = $packages->map(function($package) use ($date) {
                // Get inventory for this package on this date
                $inventory = $this->dayTourInventoryService->getOrCreateInventory($package->id, $date);
                
                // Calculate pricing based on pricing_type - use raw prices from admin (already include VAT)
                if ($package->pricing_type === 'total') {
                    // Total package price - divide by max guests to get per person rate
                    $pricePerPerson = $package->price / $package->max_guests;
                    $totalPrice = $package->price;
                    $priceDisplay = $this->formatPrice($package->price) . ' total';
                    $priceBreakdown = $this->formatPrice($pricePerPerson) . '/person for ' . $package->max_guests . ' persons';
                } else {
                    // Per person pricing - multiply by max guests for total
                    $pricePerPerson = $package->price;
                    $totalPrice = $package->price * $package->max_guests;
                    $priceDisplay = $this->formatPrice($package->price) . ' per person';
                    $priceBreakdown = $this->formatPrice($totalPrice) . ' total for ' . $package->max_guests . ' persons';
                }

                // Calculate VAT amount for the total price (prices already include VAT)
                $vatAmount = $this->vatService->extractVatFromInclusive($totalPrice);

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $pricePerPerson,
                    'total_price' => $totalPrice,
                    'max_guests' => $package->max_guests,
                    'pricing_type' => $package->pricing_type,
                    'price_display' => $priceDisplay,
                    'price_breakdown' => $priceBreakdown,
                    'image' => $package->image,
                    'activities' => $package->activities->map(function($activity) {
                        return [
                            'id' => $activity->id,
                            'name' => $activity->name
                        ];
                    }),
                    // VAT information
                    'vat' => [
                        'rate' => $this->vatService->getVatInfo()['rate_percentage'],
                        'amount' => $vatAmount,
                    ],
                    // Inventory data
                    'inventory' => [
                        'total_slots' => $inventory->max_groups,
                        'available_slots' => $inventory->available_groups,
                        'booked_slots' => $inventory->booked_groups,
                        'is_available' => $inventory->isAvailable(),
                        'is_blocked' => $inventory->is_blocked,
                        'status' => $inventory->status_color
                    ]
                ];
            });
        }

        return response()->json([
            'available' => count($packagesData) > 0,
            'date' => $date,
            'packages' => $packagesData,
            'vat_info' => [
                'rate' => $this->vatService->getVatInfo()['rate_percentage'],
                'label' => $this->vatService->getVatInfo()['label'],
                'description' => $this->vatService->getVatInfo()['description'],
            ],
            'message' => count($packagesData) === 0 ? 'No packages available at the moment. Please check back later.' : null
        ]);
    }

    /**
     * Store availability selection and redirect to activities
     * Supports multiple packages like staycation
     */
    public function storeAvailability(Request $request)
    {
        // Support both single package (legacy) and multiple packages (new)
        if ($request->has('selected_packages')) {
            // New: Multiple packages selection (like staycation)
            $validated = $request->validate([
                'selected_packages' => 'required|array|min:1',
                'selected_packages.*.id' => 'required|integer',
                'selected_packages.*.name' => 'required|string',
                'selected_packages.*.price' => 'required|numeric|min:0',
                'selected_packages.*.guests' => 'required|integer|min:1',
                'selected_packages.*.quantity' => 'nullable|integer|min:1',
            ]);

            $visitDate = Session::get('selected_dayvisit_date') ?? now()->format('Y-m-d');
            $selectedPackages = $validated['selected_packages'];

            // Check inventory for all selected packages
            $date = $visitDate;
            foreach ($selectedPackages as $package) {
                $inventory = $this->dayTourInventoryService->getOrCreateInventory($package['id'], $date);
                $requestedSlots = $package['quantity'] ?? 1;

                if (!$inventory->isAvailable() || $inventory->available_groups < $requestedSlots) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sorry, {$package['name']} is no longer available for the selected date. Please choose another package or reduce quantity."
                    ], 422);
                }
            }

            // Normalize packages data (similar to staycation accommodations)
            $normalizedPackages = [];
            $totalGuests = 0;
            $totalPrice = 0;

            foreach ($selectedPackages as $key => $package) {
                $packageId = $package['id'];
                $quantity = $package['quantity'] ?? 1;
                $guests = $package['guests'];
                $pricePerPerson = $package['price'];
                $packageTotal = $pricePerPerson * $guests * $quantity;

                $normalizedPackages[$key] = [
                    'package_id' => $packageId,
                    'name' => $package['name'],
                    'guests' => $guests,
                    'quantity' => $quantity,
                    'price_per_person' => $pricePerPerson,
                    'total_price' => $packageTotal,
                ];

                $totalGuests += $guests * $quantity;
                $totalPrice += $packageTotal;
            }

            // Store in session (similar to staycation structure)
            $bookingData = [
                'booking_type' => 'dayvisit',
                'visit_date' => $visitDate,
                'packages' => $normalizedPackages,
                'total_guests' => $totalGuests,
                'total_price' => $totalPrice,
                'package_count' => count($normalizedPackages),
            ];

            Session::put('dayvisit_booking_data', $bookingData);
            Session::put('dayvisit_selected_packages', $normalizedPackages);

            return response()->json([
                'success' => true,
                'redirect' => route('dayvisit.activities')
            ]);
        } else {
            // Legacy: Single package selection
            $validated = $request->validate([
                'guests' => 'required|integer|min:1|max:20',
                'package' => 'required',
                'package_name' => 'required|string',
                'price' => 'required|numeric|min:0'
            ]);

            // Add visit date to validated data
            $validated['visit_date'] = Session::get('selected_dayvisit_date');

            // Check inventory availability before allowing booking
            $date = $validated['visit_date'] ?? now()->format('Y-m-d');
            $inventory = $this->dayTourInventoryService->getOrCreateInventory($validated['package'], $date);

            if (!$inventory->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, this package is no longer available for the selected date. Please choose another date or package.'
                ], 422);
            }

            // Store selection in session
            Session::put('dayvisit_availability', $validated);

            // Create minimal booking data and proceed directly to activities
            $bookingData = [
                'first_name' => 'Guest',
                'last_name' => null,
                'email' => 'guest@example.com',
                'phone' => null,
                'guests' => $validated['guests'],
                'package' => $validated['package_name'],
                'package_id' => $validated['package'],
                'price' => $validated['price'] * $validated['guests'],
                'booking_date' => $validated['visit_date'],
                'special_requests' => '',
                'type' => 'day-visit'
            ];

            Session::put('dayvisit_booking_data', $bookingData);

            return response()->json([
                'success' => true,
                'redirect' => route('dayvisit.activities')
            ]);
        }
    }

    /**
     * Show activities selection page
     */
    public function activities()
    {
        $bookingData = Session::get('dayvisit_booking_data');
        if (!$bookingData) {
            return redirect()->route('dayvisit.index')->with('error', 'No booking data found. Please start booking again.');
        }

        $activities = $this->bookingService->getActivitiesData();
        return view('booking.dayvisit-activities', ['bookingData' => $bookingData, 'activities' => $activities]);
    }

    /**
     * Get day visit activities API
     */
    public function getActivitiesApi()
    {
        $activities = $this->bookingService->getActivitiesData();
        
        // Return raw activity prices (no VAT added) - VAT will be shown in booking summary only
        
        return response()->json([
            'activities' => $activities,
            'vat_info' => [
                'rate' => $this->vatService->getVatInfo()['rate_percentage'],
                'label' => $this->vatService->getVatInfo()['label'],
                'description' => $this->vatService->getVatInfo()['description'],
            ],
        ]);
    }

    /**
     * Store selected activities
     */
    public function storeActivities(Request $request)
    {
        $bookingData = Session::get('dayvisit_booking_data');
        if (!$bookingData) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        $request->validate(ValidationService::activitiesRules());
        $this->bookingService->saveActivities($request->activities, 'dayvisit_selected_activities');
        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    /**
     * Show payment page
     */
    public function payment()
    {
        $bookingData = Session::get('dayvisit_booking_data');
        if (!$bookingData) {
            return redirect()->route('dayvisit.index')->with('error', 'No booking data found. Please start booking again.');
        }

        // Fix package name - if it's just an ID, look up the actual name
        if (isset($bookingData['package']) && is_numeric($bookingData['package'])) {
            $dayTour = DayTour::find($bookingData['package']);
            if ($dayTour) {
                $bookingData['package'] = $dayTour->name;
                // Update session with correct name
                Session::put('dayvisit_booking_data', $bookingData);
            }
        }

        return view('booking.dayvisit-payment', ['bookingData' => $bookingData]);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $bookingData = Session::get('dayvisit_booking_data');
        if (!$bookingData) {
            return redirect()->route('dayvisit.index')->with('error', 'No booking data found. Please start booking again.');
        }

        return $this->paymentService->processDayvisitPayment($bookingData, $request->all());
    }

    /**
     * Initiate PayMongo checkout for day visit payment
     */
    public function initiatePayment(Request $request)
    {
        $bookingData = Session::get('dayvisit_booking_data');
        if (!$bookingData) {
            return redirect()->route('dayvisit.index')->with('error', 'No booking data found. Please start booking again.');
        }

        // Validate customer information
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:paymongo,qr,gcash,paymaya,bank,qrph,grab_pay,billease',
        ]);

        // Check inventory one more time before initiating payment
        $date = $bookingData['booking_date'] ?? now()->format('Y-m-d');
        $packageId = $bookingData['package_id'] ?? null;
        
        if ($packageId) {
            $inventory = $this->dayTourInventoryService->getOrCreateInventory($packageId, $date);
            
            if (!$inventory->isAvailable()) {
                return redirect()->route('dayvisit.availability')
                    ->with('error', 'Sorry, this package is no longer available for the selected date. Please choose another date or package.');
            }
        }

        // Calculate grand total - prices from admin are VAT-exclusive, need to add VAT
        // Support both legacy single-package ('price') and new multi-package ('total_price') data structures
        if (isset($bookingData['total_price'])) {
            // New multi-package structure (from availability page selection)
            $packagePrice = (float) $bookingData['total_price'];
        } else {
            // Legacy single-package structure or fallback
            $packagePrice = (float) ($bookingData['price'] ?? 0);
        }
        
        $activitiesTotal = collect(Session::get('dayvisit_selected_activities', []))->sum(function($activity) {
            $price = $activity['price'] ?? 0;
            return $price * ($activity['guests'] ?? 1);
        });
        
        // Prices are VAT-exclusive, calculate VAT breakdown and add VAT to get final amount
        $subtotal = $packagePrice + $activitiesTotal;
        $vatBreakdown = $this->vatService->generateBreakdown($subtotal, false); // false = prices are VAT-exclusive
        $grandTotal = $vatBreakdown['gross_amount']; // This includes VAT

        // Ensure minimum amount (PayMongo requires at least 100 centavos = 1 PHP)
        if ($grandTotal < 1) {
            $grandTotal = 1;
        }

        // Build line items for PayMongo - prices need to include VAT for correct display
        $lineItems = [];
        
        // Handle both multi-package and legacy single-package flows
        if (isset($bookingData['packages']) && is_array($bookingData['packages'])) {
            // Multi-package flow: create a line item for each package slot
            $selectedPackages = Session::get('dayvisit_selected_packages', $bookingData['packages']);
            foreach ($selectedPackages as $package) {
                $packageNameToShow = $package['name'] ?? 'Day Visit Package';
                $pricePerPersonExclusive = (float) ($package['price_per_person'] ?? 0);
                $vatService = app(\App\Services\VatService::class);
                $vatRate = $vatService->getCurrentVatRate();
                $pricePerPersonWithVat = round($pricePerPersonExclusive * (1 + $vatRate), 2);
                $guests = (int) ($package['guests'] ?? 1);
                $quantity = (int) ($package['quantity'] ?? 1);
                
                $lineItems[] = [
                    'name' => $packageNameToShow,
                    'unit_price' => $pricePerPersonWithVat,
                    'quantity' => $guests * $quantity,  // Total persons for this package slot
                ];
            }
        } else {
            // Legacy single-package flow
            $packageName = isset($bookingData['package']) && !empty($bookingData['package']) ? (string) $bookingData['package'] : 'Day Visit Package';
            
            // Calculate package price with VAT
            $vatService = app(\App\Services\VatService::class);
            $vatRate = $vatService->getCurrentVatRate();
            $packagePriceWithVat = round($packagePrice * (1 + $vatRate), 2);
            
            // Ensure package price is at least 1 PHP if it's the only item
            if ($packagePriceWithVat < 1 && empty($selectedActivities)) {
                $packagePriceWithVat = 1;
            }
            
            $lineItems[] = [
                'name' => $packageName,
                'unit_price' => $packagePriceWithVat,
                'quantity' => 1,
            ];
        }

        // Add activity line items with VAT included
        $selectedActivities = Session::get('dayvisit_selected_activities', []);
        foreach ($selectedActivities as $activity) {
            if (!empty($activity) && isset($activity['name']) && !empty($activity['name']) && isset($activity['price'])) {
                $guestCount = (int) ($activity['guests'] ?? 1);
                $unitPriceExclusive = (float) $activity['price'];
                $vatService = app(\App\Services\VatService::class);
                $vatRate = $vatService->getCurrentVatRate();
                $unitPriceWithVat = round($unitPriceExclusive * (1 + $vatRate), 2); // Add VAT
                $activityName = (string) $activity['name'];
                $lineItems[] = [
                    'name' => $activityName,
                    'unit_price' => $unitPriceWithVat,
                    'quantity' => $guestCount,
                ];
            }
        }

        // Store VAT information in session for receipt
        Session::put('dayvisit_vat', $vatBreakdown);

        // Store booking and payment data in session
        Session::put('dayvisit_payment_data', [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'special_requests' => $request->special_requests,
            'cancellation_policy' => $request->cancellation_policy,
            'payment_method' => $request->payment_method,
            'expected_amount_centavos' => (int) round($grandTotal * 100),
        ]);

        $successUrl = url('/booking/dayvisit/payment/success?checkout_id={CHECKOUT_ID}');
        $failedUrl = url('/booking/dayvisit/payment/failed');

        // Generate a reference id for this checkout
        $referenceId = 'TXN-' . strtoupper(uniqid());
        Session::put('dayvisit_payment_data.reference_id', $referenceId);

        // Create pending booking record in DB before redirecting to PayMongo
        // This ensures webhook can find the booking even if cache/session is lost
        $paymentDataForPending = array_merge(Session::get('dayvisit_payment_data', []), [
            'reference_id' => $referenceId,
            'expected_amount_centavos' => (int) round($grandTotal * 100),
        ]);
        $pendingBooking = $this->paymentService->createPendingBooking($bookingData, $paymentDataForPending, 'dayvisit');

        Cache::put('paymongo_checkout_context:' . $referenceId, [
            'type' => 'dayvisit',
            'booking_data' => $bookingData,
            'payment_data' => $paymentDataForPending,
            'created_at' => now()->toDateTimeString(),
        ], now()->addHours(24));

        try {
            \Illuminate\Support\Facades\Log::info('Creating PayMongo checkout for day visit', [
                'grand_total' => $grandTotal,
                'line_items_count' => count($lineItems),
                'line_items' => $lineItems,
                'reference_id' => $referenceId,
            ]);

            $checkoutMetadata = array_filter([
                'booking_type' => 'dayvisit',
                'booking_date' => $bookingData['booking_date'] ?? null,
                'package_id' => $bookingData['package_id'] ?? null,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ], fn ($value) => $value !== null && $value !== '');

            $checkout = $this->paymentService->createCheckout(
                (int)round($grandTotal * 100), 
                'dayvisit', 
                $successUrl, 
                $failedUrl, 
                $lineItems, 
                $referenceId, 
                $request->email,
                $checkoutMetadata
            );
            
            \Illuminate\Support\Facades\Log::info('PayMongo checkout created successfully', [
                'checkout_id' => $checkout->id ?? 'unknown',
                'checkout_url' => $checkout->checkout_url ?? 'unknown',
            ]);

            if (!empty($checkout->id)) {
                Cache::put('paymongo_checkout_reference:' . $checkout->id, $referenceId, now()->addHours(24));
                Cache::put('paymongo_checkout_context:' . $referenceId, [
                    'type' => 'dayvisit',
                    'booking_data' => $bookingData,
                    'payment_data' => array_merge(Session::get('dayvisit_payment_data', []), [
                        'reference_id' => $referenceId,
                        'checkout_session_id' => $checkout->id,
                        'expected_amount_centavos' => (int) round($grandTotal * 100),
                    ]),
                    'created_at' => now()->toDateTimeString(),
                ], now()->addHours(24));

                // Update pending booking with checkout session ID
                $pendingBooking->update(['paymongo_checkout_id' => $checkout->id]);
            }
            
            return redirect($checkout->checkout_url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create PayMongo checkout: ' . $e->getMessage());
            // Mark pending booking as failed
            $pendingBooking->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
            return redirect()->back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful PayMongo payment
     */
    public function paymentSuccess(Request $request)
    {
        $checkoutId = $request->query('checkout_id');

        if (!$checkoutId) {
            return redirect()->route('dayvisit.payment')->with('error', 'Invalid payment session.');
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $checkoutAttributes = $data['data']['attributes'] ?? [];
            $payment = is_array($checkoutAttributes['payments'][0] ?? null)
                ? $checkoutAttributes['payments'][0]
                : [];
            $paymentAttributes = is_array($payment['attributes'] ?? null)
                ? $payment['attributes']
                : [];
            $paymentStatus = $paymentAttributes['status']
                ?? ($checkoutAttributes['payment_status'] ?? data_get($checkoutAttributes, 'payment_intent.attributes.status'));
            $paidAmountCentavos = $paymentAttributes['amount'] ?? ($checkoutAttributes['amount'] ?? null);

            if (in_array($paymentStatus, ['succeeded', 'paid'], true) && is_numeric($paidAmountCentavos)) {
                // Payment successful, process the booking
                $bookingData = Session::get('dayvisit_booking_data');
                $paymentData = Session::get('dayvisit_payment_data');

                $referenceId = $paymentData['reference_id'] ?? null;

                // Find the pending booking by transaction_id
                $existingBooking = Booking::where('transaction_id', $referenceId)
                    ->where('payment_status', 'pending')
                    ->first();

                $expectedAmountCentavos = $existingBooking
                    ? (int) round(((float) $existingBooking->total_amount) * 100)
                    : (int) ($paymentData['expected_amount_centavos'] ?? 0);
                if ($expectedAmountCentavos < 1 || (int) $paidAmountCentavos !== $expectedAmountCentavos) {
                    Log::warning('Rejected day visit payment with amount mismatch', [
                        'booking_id' => $existingBooking?->id,
                        'checkout_id' => $checkoutId,
                        'expected_amount_centavos' => $expectedAmountCentavos,
                        'paid_amount_centavos' => (int) $paidAmountCentavos,
                    ]);

                    return redirect()->route('dayvisit.payment')->with('error', 'Payment amount mismatch.');
                }

                if ($existingBooking) {
                    $paymentData['checkout_id'] = $checkoutId;
                    $paymentData['paid_amount_centavos'] = (int) $paidAmountCentavos;

                    // Confirm the pending booking
                    $this->paymentService->confirmPendingBooking($existingBooking, $paymentData);

                    // Set payment reference
                    $paymentReference = $payment['id'] ?? $checkoutId;
                    $existingBooking->update([
                        'payment_reference' => $paymentReference,
                    ]);

                    Cache::forget('paymongo_checkout_reference:' . $checkoutId);
                    if (!empty($referenceId)) {
                        Cache::forget('paymongo_checkout_context:' . $referenceId);
                    }

                    // Clear session
                    Session::forget(['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_payment_data']);

                    return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
                }

                // Fallback: booking not found via reference, try legacy flow
                if (!$bookingData || !$paymentData) {
                    return redirect()->route('dayvisit.payment')->with('error', 'Booking data not found.');
                }

                // Book the inventory slot
                $date = $bookingData['booking_date'] ?? now()->format('Y-m-d');
                $packageId = $bookingData['package_id'] ?? null;
                
                if ($packageId) {
                    $slotBooked = $this->dayTourInventoryService->bookSlot(
                        $packageId, 
                        $date, 
                        $checkoutId
                    );
                    
                    if (!$slotBooked) {
                        \Illuminate\Support\Facades\Log::error('Failed to book inventory slot after payment', [
                            'package_id' => $packageId,
                            'date' => $date,
                            'checkout_id' => $checkoutId
                        ]);
                    }
                }

                // Process the payment
                $paymentData['checkout_id'] = $checkoutId;
                $paymentData['paid_amount_centavos'] = (int) $paidAmountCentavos;
                $result = $this->paymentService->processDayvisitPayment($bookingData, $paymentData);

                $referenceId = $paymentData['reference_id'] ?? null;
                if (!empty($referenceId)) {
                    Cache::forget('paymongo_checkout_context:' . $referenceId);
                }
                Cache::forget('paymongo_checkout_reference:' . $checkoutId);

                // Clear session
                Session::forget(['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_payment_data']);

                return $result;
            } else {
                return redirect()->route('dayvisit.payment')->with('error', 'Payment was not successful.');
            }
        } catch (\Exception $e) {
            Log::error('Day visit payment verification failed', [
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dayvisit.payment')->with('error', 'Payment verification failed.');
        }
    }

    /**
     * Handle failed PayMongo payment
     */
    public function paymentFailed(Request $request)
    {
        $checkoutId = $request->query('checkout_id');

        \Illuminate\Support\Facades\Log::info('PayMongo payment failed callback received', [
            'checkout_id' => $checkoutId,
        ]);

        if ($checkoutId) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}", [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $data = json_decode($response->getBody(), true);
                $paymentIntentStatus = data_get($data, 'data.attributes.payment_intent.status', 'unknown');

                // Log the failure reason
                \Illuminate\Support\Facades\Log::warning('PayMongo day visit payment failed', [
                    'checkout_id' => $checkoutId,
                    'status' => $paymentIntentStatus,
                ]);

                // If payment actually succeeded, redirect to success handler
                if ($paymentIntentStatus === 'succeeded') {
                    \Illuminate\Support\Facades\Log::info('Payment actually succeeded, redirecting to success handler', [
                        'checkout_id' => $checkoutId,
                    ]);
                    return redirect()->route('dayvisit.paymentSuccess', ['checkout_id' => $checkoutId]);
                }

                $this->paymentService->failPendingBookingForCheckout($checkoutId, null, 'failed', 'paymongo_failed_callback');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PayMongo checkout verification failed on failure: ' . $e->getMessage());
            }
        }

        return redirect()->route('dayvisit.payment')->with('error', 'Payment was cancelled or failed. Please try again.');
    }
}
