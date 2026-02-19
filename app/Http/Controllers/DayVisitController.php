<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\ValidationService;
use App\Services\InventoryService;
use App\Models\DayTour;
use Carbon\Carbon;

class DayVisitController extends Controller
{
    protected $bookingService;
    protected $paymentService;
    protected $inventoryService;

    public function __construct(BookingService $bookingService, PaymentService $paymentService, InventoryService $inventoryService)
    {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
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
     * Get day visit availability API
     */
    public function getAvailabilityApi(Request $request)
    {
        $guests = $request->query('guests', 1);

        // Get available packages
        $packages = DayTour::all();
        $packagesData = [];

        if ($packages->count() > 0) {
            $packagesData = $packages->map(function($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $package->price,
                    'image' => $package->image
                ];
            });
        } else {
            // Fallback to hardcoded packages if no packages exist
            $packagesData = [
                [
                    'id' => 1,
                    'name' => 'Day Visit Package with Free Drinks',
                    'description' => 'Enjoy a day of nature, fresh air, and free drinks',
                    'price' => 500,
                    'image' => null
                ]
            ];
        }

        return response()->json([
            'available' => true,
            'packages' => $packagesData
        ]);
    }

    /**
     * Store availability selection and redirect to activities
     */
    public function storeAvailability(Request $request)
    {
        $validated = $request->validate([
            'guests' => 'required|integer|min:1|max:20',
            'package' => 'required',
            'package_name' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        // Add visit date to validated data
        $validated['visit_date'] = Session::get('selected_dayvisit_date');

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
        return response()->json($activities);
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

        // Calculate grand total
        $activitiesTotal = collect(Session::get('dayvisit_selected_activities', []))->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });
        $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;

        // Ensure minimum amount (PayMongo requires at least 100 centavos = 1 PHP)
        if ($grandTotal < 1) {
            $grandTotal = 1;
        }

        // Build line items for PayMongo
        $lineItems = [];
        
        // Add package line item - ensure name is a string and not empty
        $packageName = isset($bookingData['package']) && !empty($bookingData['package']) ? (string) $bookingData['package'] : 'Day Visit Package';
        $packagePrice = (float) ($bookingData['price'] ?? 0);
        
        // Ensure package price is at least 1 PHP if it's the only item
        if ($packagePrice < 1 && empty($selectedActivities)) {
            $packagePrice = 1;
        }
        
        $lineItems[] = [
            'name' => $packageName,
            'unit_price' => $packagePrice,
            'quantity' => 1,
        ];

        // Add activity line items - ensure names are strings
        $selectedActivities = Session::get('dayvisit_selected_activities', []);
        foreach ($selectedActivities as $activity) {
            if (!empty($activity) && isset($activity['name']) && !empty($activity['name']) && isset($activity['price'])) {
                $guestCount = (int) ($activity['guests'] ?? 1);
                $unit = (float) $activity['price'];
                $activityName = (string) $activity['name'];
                $lineItems[] = [
                    'name' => $activityName,
                    'unit_price' => $unit,
                    'quantity' => $guestCount,
                ];
            }
        }

        // Store booking and payment data in session
        Session::put('dayvisit_payment_data', [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'special_requests' => $request->special_requests,
            'cancellation_policy' => $request->cancellation_policy,
            'payment_method' => $request->payment_method,
        ]);

        $successUrl = url('/booking/dayvisit/payment/success?checkout_id={CHECKOUT_ID}');
        $failedUrl = url('/booking/dayvisit/payment/failed');

        // Generate a reference id for this checkout
        $referenceId = 'TXN-' . strtoupper(uniqid());
        Session::put('dayvisit_payment_data.reference_id', $referenceId);

        try {
            \Illuminate\Support\Facades\Log::info('Creating PayMongo checkout for day visit', [
                'grand_total' => $grandTotal,
                'line_items_count' => count($lineItems),
                'line_items' => $lineItems,
                'reference_id' => $referenceId,
            ]);

            $checkout = $this->paymentService->createCheckout(
                (int)round($grandTotal * 100), 
                'dayvisit', 
                $successUrl, 
                $failedUrl, 
                $lineItems, 
                $referenceId, 
                $request->email
            );
            
            \Illuminate\Support\Facades\Log::info('PayMongo checkout created successfully', [
                'checkout_id' => $checkout->id ?? 'unknown',
                'checkout_url' => $checkout->checkout_url ?? 'unknown',
            ]);
            
            return redirect($checkout->checkout_url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create PayMongo checkout: ' . $e->getMessage());
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
            $checkout = (object) $data['data']['attributes'];

            if ($checkout->payment_intent && $checkout->payment_intent->status === 'succeeded') {
                // Payment successful, process the booking
                $bookingData = Session::get('dayvisit_booking_data');
                $paymentData = Session::get('dayvisit_payment_data');

                if (!$bookingData || !$paymentData) {
                    return redirect()->route('dayvisit.payment')->with('error', 'Booking data not found.');
                }

                // Process the payment
                $paymentData['checkout_id'] = $checkoutId;
                $result = $this->paymentService->processDayvisitPayment($bookingData, $paymentData);

                // Clear session
                Session::forget(['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_payment_data']);

                return $result; // This should be the redirect to confirmation
            } else {
                return redirect()->route('dayvisit.payment')->with('error', 'Payment was not successful.');
            }
        } catch (\Exception $e) {
            return redirect()->route('dayvisit.payment')->with('error', 'Payment verification failed: ' . $e->getMessage());
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
            'all_params' => $request->all(),
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
                $checkout = (object) $data['data']['attributes'];

                // Log the failure reason
                \Illuminate\Support\Facades\Log::warning('PayMongo day visit payment failed', [
                    'checkout_id' => $checkoutId,
                    'status' => $checkout->payment_intent ? $checkout->payment_intent->status : 'unknown',
                    'payment_intent' => $checkout->payment_intent ?? null,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PayMongo checkout verification failed on failure: ' . $e->getMessage());
            }
        }

        // Check if this was actually a success that got redirected to failed URL
        // Sometimes PayMongo redirects to cancel_url even on success
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
                $checkout = (object) $data['data']['attributes'];

                // If payment actually succeeded, redirect to success handler
                if ($checkout->payment_intent && $checkout->payment_intent->status === 'succeeded') {
                    \Illuminate\Support\Facades\Log::info('Payment actually succeeded, redirecting to success handler', [
                        'checkout_id' => $checkoutId,
                    ]);
                    return redirect()->route('dayvisit.paymentSuccess', ['checkout_id' => $checkoutId]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error checking payment status on failed callback: ' . $e->getMessage());
            }
        }

        return redirect()->route('dayvisit.payment')->with('error', 'Payment was cancelled or failed. Please try again.');
    }
}
