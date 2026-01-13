<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\BookingConfirmation;
use DateTime;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\ValidationService;

use App\Models\StaycationAccommodation;
use App\Models\Staycation;
use App\Models\Holiday;
use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakPeriod;
use App\Models\PeakType;
use App\Services\PricingService;


class BookingController extends Controller
{
    protected $bookingService;
    protected $paymentService;
    protected $inventoryService;

    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        InventoryService $inventoryService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
    }
    public function debugSession()
    {
        // Dump the entire session
        dd(session()->all());
    }

    private function getBookingActivities($type)
    {
        $config = self::BOOKING_CONFIG[$type];
        $data = session($config['session_key']);
        if (!$data) {
            return redirect()->route($config['activities_error_route'])->with('error', 'No booking data found. Please start booking again.');
        }
        $activities = $this->bookingService->getActivitiesData();
        return view($config['activities_view'], [$config['var_name'] => $data, 'activities' => $activities]);
    }

    private function storeBookingActivities(Request $request, $type, $validationRules, $saveKey)
    {
        $config = self::BOOKING_CONFIG[$type];
        $data = session($config['session_key']);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }
        $request->validate($validationRules);
        $this->bookingService->saveActivities($request->activities, $saveKey);
        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    private function getBookingPayment($type)
    {
        $config = self::BOOKING_CONFIG[$type];
        $data = session($config['session_key']);
        if (!$data) {
            return redirect()->route($config['payment_error_route'])->with('error', 'No booking data found. Please start booking again.');
        }
        return view($config['payment_view'], [$config['var_name'] => $data]);
    }

    private function getConfirmationData(string $type): ?array
    {
        $sessionKeys = [
            'staycation' => [
                'booking_data' => 'staycation_booking_data',
                'selected_activities' => 'staycation_selected_activities',
                'grand_total' => 'staycation_grand_total',
                'customer_info' => 'staycation_customer_info',
                'is_type' => 'isStaycation'
            ],
            'dayvisit' => [
                'booking_data' => 'dayvisit_booking_data',
                'selected_activities' => 'dayvisit_selected_activities',
                'grand_total' => 'dayvisit_grand_total',
                'customer_info' => 'dayvisit_customer_info',
                'is_type' => 'isDayvisit'
            ],
            'regular' => [
                'booking_data' => 'booking_data',
                'selected_activities' => 'selected_activities',
                'grand_total' => 'grand_total',
                'customer_info' => 'customer_info',
                'is_type' => null
            ]
        ];

        if (!isset($sessionKeys[$type])) {
            return null;
        }

        $keys = $sessionKeys[$type];
        $bookingData = session($keys['booking_data']);

        if (!$bookingData) {
            return null;
        }

        $data = [
            'bookingData' => $bookingData,
            'selectedActivities' => session($keys['selected_activities'], []),
        ];

        if (isset($keys['grand_total'])) {
            $data['grandTotal'] = session($keys['grand_total'], 0);
            $data['customerInfo'] = session($keys['customer_info'], []);
        } else {
            // Regular booking calculation
            $data['grandTotal'] = ($bookingData['price'] ?? 0) + collect($data['selectedActivities'])->sum(function($activity) {
                return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
            });
        }

        // Set isDayvisit for regular bookings that are day visits
        if ($type === 'regular' && isset($bookingData['type']) && $bookingData['type'] === 'day-visit') {
            $data['isDayvisit'] = true;
        }

        if ($keys['is_type']) {
            $data[$keys['is_type']] = true;
        }

        // Clear sessions
        $sessionsToClear = [$keys['booking_data'], $keys['selected_activities']];
        if (isset($keys['grand_total'])) {
            $sessionsToClear = array_merge($sessionsToClear, [$keys['grand_total'], $keys['customer_info']]);
        }
        session()->forget($sessionsToClear);

        return $data;
    }

    public function create(Request $request)
    {
        // Get booking type from URL or default to day-visit
        $type = $request->get('type', 'day-visit');

        // Handle different type variations
        $allowedTypes = ['day-visit', 'room', 'daytour'];
        if ($type === 'daytour') {
            $type = 'day-visit'; // Map daytour to day-visit
        }
        if (!in_array($type, $allowedTypes)) {
            $type = 'day-visit'; // Fallback to default
        }

        // Get package details
        $package = $request->get('package', 'Day Visit Package');
        $price = $request->get('price', '1000');
        $price = preg_replace('/[^\d.]/', '', $price); // Remove currency symbols

        // Set guest limits based on package type
        $max_guests = 50;
        $default_guests = 1;

        if (str_contains($package, 'Family Fun Package')) {
            $max_guests = 6; // Family packages limited to 6 guests
            $default_guests = 6;
        }

        session(['max_guests' => $max_guests]);

        // Get booking settings and current inventory
        $settings = (object) [
            'day_visit_capacity' => 10,
            'room_capacity' => 5,
        ];

        // Generate inventory for the next 30 days including weekends
        $inventory = [];
        $inventory['day-visit'] = [];
        $inventory['room'] = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            $dayOfWeek = now()->addDays($i)->dayOfWeek;
            // Weekends have slightly lower availability
            $dayVisitSlots = ($dayOfWeek == 0 || $dayOfWeek == 6) ? 6 : 8;
            $roomSlots = ($dayOfWeek == 0 || $dayOfWeek == 6) ? 2 : 3;
            $inventory['day-visit'][$date] = $dayVisitSlots;
            $inventory['room'][$date] = $roomSlots;
        }

        return view('booking.reservation', compact('type', 'package', 'price', 'settings', 'inventory', 'max_guests', 'default_guests'));
    }

    public function store(Request $request)
    {
        // Validate booking data
        $validated = $request->validate(ValidationService::bookingRules(session('max_guests', 50)));

        // Check availability
        $type = $validated['type'];
        $date = $validated['booking_date'];

        if (!$this->inventoryService->isAvailable($type, $date)) {
            return redirect()->back()->with('error', 'No availability for the selected date.');
        }

        // Update inventory
        $this->inventoryService->reduceInventory($type, $date);

        // Calculate price for day visits (per person except family packages)
        if ($validated['type'] === 'day-visit' && !str_contains($validated['package'], 'Family Fun Package')) {
            $validated['price'] = $validated['price'] * $validated['guests'];
        }

        // Save booking data and go to activities
        session(['booking_data' => $validated]);
        return redirect()->route('booking.activities');
    }

    public function activities()
    {
        return $this->getBookingActivities('regular');
    }

    public function getActivitiesApi()
    {
        $activities = $this->bookingService->getActivitiesData();
        return response()->json($activities);
    }

    public function storeActivities(Request $request)
    {
        return $this->storeBookingActivities($request, 'regular', ValidationService::activitiesRules(), 'selected_activities');
    }

    public function staycationActivities()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete accommodation selection first.');
        }

        $activities = $this->bookingService->getActivitiesData();

        return view('booking.staycation-activities', compact('booking', 'activities'));
    }

    public function storeStaycationActivities(Request $request)
    {
        $booking = session('booking', []);

        if (empty($booking)) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        // For staycations, activities are optional - allow empty array
        $staycationValidationRules = [
            'activities' => 'array', // Allow empty array for "proceed without activities"
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined,custom',
        ];

        try {
            $request->validate(ValidationService::staycationActivitiesRules());
            $this->bookingService->saveActivities($request->activities ?? [], 'booking.selected_activities');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving. Please try again.',
                'errors' => $e->errors()
            ], 422);
        }

        // Note: We need to update the session booking array, so handle differently
        $booking['selected_activities'] = $request->activities ?? [];

        // Calculate final totals using dynamic pricing
        $checkinDate = $booking['checkin'] ?? null;
        $checkoutDate = $booking['checkout'] ?? null;
        $datesSelected = !empty($checkinDate) && !empty($checkoutDate);

        $accommodationTotal = 0;
        if ($datesSelected && !empty($booking['accommodations'])) {
            $accommodationTotal = $this->calculateStaycationTotal($booking['accommodations'], $checkinDate, $checkoutDate);
        }

        $activitiesTotal = collect($request->activities)->sum(function($activity) {
            return 500 * ($activity['guests'] ?? 1);
        });

        // Store final totals in session
        $booking['final_totals'] = [
            'accommodation_total' => $accommodationTotal,
            'activities_total' => $activitiesTotal,
            'grand_total' => $accommodationTotal + $activitiesTotal
        ];

        $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [3]));
        $booking['current_step'] = 4;
        session(['booking' => $booking]);

        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    /**
     * Calculate total price for staycation accommodations
     */
    private function calculateStaycationTotal(array $accommodations, string $checkinDate, string $checkoutDate): float
    {
        $total = 0;

        foreach ($accommodations as $acc) {
            if (empty($acc['room_id'])) continue;

            $staycation = Staycation::find($acc['room_id']);
            if (!$staycation || empty($staycation->price_configs)) continue;

            $roomTotal = $this->calculateStaycationRoomPrice($staycation, $checkinDate, $checkoutDate, $acc);
            $total += $roomTotal;
        }

        return $total;
    }

    /**
     * Calculate price for a single staycation room
     */
    private function calculateStaycationRoomPrice(Staycation $staycation, string $checkinDate, string $checkoutDate, array $acc): float
    {
        $total = 0;
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);

        // Get holiday dates
        $holidayDates = Holiday::whereBetween('date', [$checkinDate, $checkout->copy()->subDay()->format('Y-m-d')])
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Get weekend day IDs
        $weekendDayIds = DayTypeDay::whereHas('dayType', fn($q) => $q->where('name', 'Weekend'))
            ->pluck('weekday')
            ->toArray();

        $current = $checkin->copy();
        while ($current->lt($checkout)) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = (int)$current->dayOfWeek;

            // Determine day type: Holiday > Weekend > Weekday
            $dayType = 'nonPeak'; // default
            if (in_array($dateStr, $holidayDates)) {
                $dayType = 'superPeak';
            } elseif (in_array($dayOfWeek, $weekendDayIds)) {
                $dayType = 'peak';
            }

            // Find applicable price config for this date
            $rate = $this->getStaycationRateForDate($staycation->price_configs, $current, $dayType);

            if ($rate > 0) {
                $total += $rate;
            }

            $current->addDay();
        }

        // Add extra mattress fee (assuming ₱400 per night per mattress)
        $extraMattresses = $acc['extra_mattress'] ?? 0;
        $nights = $checkin->diffInDays($checkout);
        $total += $extraMattresses * 400 * $nights;

        // Add pet fees
        if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
            $total += 2000 * $acc['num_pets'];
        }

        return $total;
    }

    /**
     * Get the applicable rate for a staycation on a specific date
     */
    private function getStaycationRateForDate(array $priceConfigs, \Carbon\Carbon $date, string $dayType): float
    {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = (int)$date->dayOfWeek;

        foreach ($priceConfigs as $config) {
            $applies = false;

            if (($config['type'] ?? 'day') === 'day') {
                // Check if day matches
                $configDays = $config['days'] ?? [];
                if (in_array($date->format('D'), $configDays)) {
                    $applies = true;
                }
            } elseif (($config['type'] ?? 'day') === 'date') {
                // Check specific dates
                $configDates = $config['dates'] ?? '';
                if (str_contains($configDates, $dateStr) || $configDates === $dateStr) {
                    $applies = true;
                }
            }

            if ($applies) {
                $rates = $config['rates'] ?? [];
                return (float)($rates[$dayType] ?? 0);
            }
        }

        // Fallback to first config's rate
        if (!empty($priceConfigs)) {
            $rates = $priceConfigs[0]['rates'] ?? [];
            return (float)($rates[$dayType] ?? 0);
        }

        return 0;
    }

    public function staycationPayment()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete your staycation booking first.');
        }

        return view('booking.staycation-payment', compact('booking'));
    }

    public function payment()
    {
        return $this->getBookingPayment('regular');
    }

    public function confirmation()
    {
        // Handle staycation confirmation separately since it uses 'booking' session
        $staycationBooking = session('booking', []);
        if (!empty($staycationBooking) && isset($staycationBooking['booking_type']) && $staycationBooking['booking_type'] === 'staycation') {
            $data = [
                'bookingData' => $staycationBooking,
                'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
                'grandTotal' => $staycationBooking['final_totals']['grand_total'] ?? 0,
                'customerInfo' => session('staycation_customer_info', []),
                'isStaycation' => true
            ];
            session()->forget(['booking', 'staycation_customer_info']);
            return view('booking.confirmation', $data);
        }

        $bookingTypes = ['staycation', 'dayvisit', 'regular'];

        foreach ($bookingTypes as $type) {
            $data = $this->getConfirmationData($type);
            if ($data) {
                // Clear sessions for this type
                $sessionsToClear = [];
                if ($type === 'dayvisit') {
                    $sessionsToClear = ['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_grand_total', 'dayvisit_customer_info'];
                } elseif ($type === 'regular') {
                    $sessionsToClear = ['booking_data', 'selected_activities', 'grand_total', 'customer_info'];
                }
                session()->forget($sessionsToClear);
                return view('booking.confirmation', $data);
            }
        }

        return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
    }


    public function processPayment(Request $request)
    {
        // Validate payment form
        $request->validate(ValidationService::paymentRules());

        // Get booking data from session
        $bookingData = session('booking_data');
        $staycationBooking = session('booking', []);
        $dayvisitBooking = session('dayvisit_booking_data');

        // Check if we have any booking data
        if (!$bookingData && empty($staycationBooking) && !$dayvisitBooking) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Define payment processors for each booking type
        $paymentProcessors = [
            [
                'condition' => !empty($staycationBooking) && $staycationBooking['booking_type'] === 'staycation',
                'processor' => fn() => $this->paymentService->processStaycationPayment($staycationBooking, $request->all())
            ],
            [
                'condition' => (bool) $dayvisitBooking,
                'processor' => fn() => $this->paymentService->processDayvisitPayment($dayvisitBooking, $request->all())
            ],
            [
                'condition' => true, // default case
                'processor' => fn() => $this->paymentService->processRegularBookingPayment($bookingData, $request->all())
            ]
        ];

        // Find and execute the appropriate payment processor
        foreach ($paymentProcessors as $processor) {
            if ($processor['condition']) {
                return $processor['processor']();
            }
        }
    }


    public function widget()
    {
        $settings = (object) config('booking.capacities');
        $remainingPerDateArray = $this->inventoryService->getAllInventory();

        return view('booking.widget', [
            'dayVisitCapacity'    => $settings->day_visit_capacity,
            'roomCapacity'        => $settings->room_capacity,
            'inventory'           => $remainingPerDateArray, // optional
        ]);
    }

    private const BOOKING_CONFIG = [
        'regular' => [
            'session_key' => 'booking_data',
            'activities_view' => 'booking.activities',
            'activities_error_route' => 'home',
            'payment_view' => 'booking.payment',
            'payment_error_route' => 'home',
            'var_name' => 'bookingData',
        ],
        'staycation' => [
            'session_key' => 'booking',
            'activities_view' => 'booking.staycation-activities',
            'activities_error_route' => 'booking.staycation',
            'payment_view' => 'booking.staycation-payment',
            'payment_error_route' => 'booking.staycation',
            'var_name' => 'booking',
        ],
        'dayvisit' => [
            'session_key' => 'dayvisit_booking_data',
            'activities_view' => 'booking.dayvisit-activities',
            'activities_error_route' => 'booking.dayvisit',
            'payment_view' => 'booking.dayvisit-payment',
            'payment_error_route' => 'booking.dayvisit',
            'var_name' => 'bookingData',
        ],
    ];

    // public function staycation(Request $request)
    // {
    //     // Clear booking session if requested (after successful booking)
    //     if ($request->has('clear')) {
    //         session()->forget('booking');
    //     }

    //     // Handle POST request to set dates and accommodations
    //     if ($request->isMethod('post')) {
    //         $request->validate(ValidationService::staycationPostRules());

    //         $booking = session('booking', []);
    //         if ($request->has('check_in') && $request->has('check_out')) {
    //             $booking['checkin'] = $request->check_in;
    //             $booking['checkout'] = $request->check_out;
    //             $booking['booking_type'] = 'staycation';
    //             $booking['completed_steps'] = [1]; // Mark step 1 as completed
    //             $booking['current_step'] = 1;
    //             $booking['timestamp'] = now()->timestamp;
    //         }
    //         if ($request->has('accommodations')) {
    //             $booking['accommodations'] = $request->input('accommodations', []);
    //             $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [2]));
    //             $booking['current_step'] = 2;
    //         }
    //         session(['booking' => $booking]);

    //         // Redirect based on request
    //         if ($request->input('skip_to_payment')) {
    //             return redirect()->route('booking.staycationPayment');
    //         } elseif ($request->input('proceed_to_activities')) {
    //             return redirect()->route('booking.staycationActivities');
    //         } else {
    //             return redirect()->route('booking.staycation');
    //         }
    //     }

    //     // Hardcoded staycations data with appropriate images
    //     $staycations = [
    //         [
    //             'id' => 1,
    //             'name' => 'Standard Glamping',
    //             'type' => 'glamping',
    //             'capacity' => 6,
    //             'adults' => 4,
    //             'children' => 2,
    //             'price' => 1200,
    //             'image' => asset('assets/images/carousel/Glamping-1.jpg'),
    //         ],
    //         [
    //             'id' => 2,
    //             'name' => 'Premium Glamping',
    //             'type' => 'glamping',
    //             'capacity' => 6,
    //             'adults' => 4,
    //             'children' => 2,
    //             'price' => 5500,
    //             'image' => asset('assets/images/carousel/Glamping-1.jpg'),
    //         ],
    //         [
    //             'id' => 3,
    //             'name' => 'Dome Tent',
    //             'type' => 'camping',
    //             'capacity' => 8,
    //             'adults' => 6,
    //             'children' => 2,
    //             'price' => 1200,
    //             'image' => asset('assets/images/carousel/Car-camping3.jpg'),
    //         ],
    //         [
    //             'id' => 4,
    //             'name' => 'Bell Tent',
    //             'type' => 'camping',
    //             'capacity' => 8,
    //             'adults' => 6,
    //             'children' => 2,
    //             'price' => 1800,
    //             'image' => asset('assets/images/carousel/Car-camping3.jpg'),
    //         ],
    //         [
    //             'id' => 5,
    //             'name' => 'BYOT',
    //             'type' => 'camping',
    //             'capacity' => 4,
    //             'adults' => 4,
    //             'children' => 2,
    //             'price' => 800,
    //             'image' => asset('assets/images/carousel/BYOT-2.jpg'),
    //         ],
    //         [
    //             'id' => 6,
    //             'name' => 'Car Camping',
    //             'type' => 'camping',
    //             'capacity' => 4,
    //             'adults' => 4,
    //             'children' => 2,
    //             'price' => 1500,
    //             'image' => asset('assets/images/carousel/Car-camping3.jpg'),
    //         ],
    //     ];

    //     // Get accommodation data from session or default
    //     $accommodationData = session('accommodation_data', []);

    //     // Check availability if dates are selected
    //     $availability = [];
    //     $booking = session('booking', []);
    //     $checkinDate = $booking['checkin'] ?? null;
    //     $checkoutDate = $booking['checkout'] ?? null;

    //     if ($checkinDate && $checkoutDate) {
    //         $staycationIds = array_column($staycations, 'id');
    //         // Call the checkAvailability method internally
    //         $availability = $this->checkAvailabilityInternal($checkinDate, $checkoutDate, $staycationIds);
    //     }

    //     // Pre-select accommodation if package is provided in query params
    //     if ($request->has('package')) {
    //         $packageName = urldecode($request->get('package'));
    //         $selectedStaycation = collect($staycations)->firstWhere('name', $packageName);
    //         if ($selectedStaycation) {
    //             $booking['accommodations'][0] = [
    //                 'staycation' => $selectedStaycation,
    //                 'guests' => $selectedStaycation['adults'] ?? 2, // Default to adults count
    //             ];
    //             session(['booking' => $booking]);
    //         }
    //     }

    //     return view('booking.staycation', compact('staycations', 'accommodationData', 'availability'));
    // }

    public function staycation(Request $request)
    {
        // Clear booking session if requested
        if ($request->has('clear')) {
            session()->forget('booking');
        }

        // Handle POST request
        if ($request->isMethod('post')) {
            $data = $request->all();

            // Handle selected_rooms from availability page
            if ($request->has('selected_rooms')) {
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'selected_rooms' => ['required', 'array', 'min:1'],
                    'selected_rooms.*.id' => ['nullable'],
                    'selected_rooms.*.adults' => ['required', 'integer', 'min:1', 'max:20'],
                    'selected_rooms.*.children' => ['nullable', 'integer', 'min:0', 'max:20'],
                    'selected_rooms.*.extra_mattress' => ['nullable', 'integer', 'min:0', 'max:8'],
                    'selected_rooms.*.pets' => ['required', 'in:yes,no'],
                    'selected_rooms.*.pet_agreement' => ['nullable', 'boolean'],
                    'selected_rooms.*.num_pets' => ['nullable', 'integer', 'min:0', 'max:2'],
                    'proceed_to_activities' => ['nullable', 'boolean'],
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid selection data.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $selectedRooms = $data['selected_rooms'];

                // Extra validation: if pets=yes => must agree + must have num_pets >= 1
                foreach ($selectedRooms as $roomId => $room) {
                    if (($room['pets'] ?? 'no') === 'yes') {
                        $agreed = (bool)($room['pet_agreement'] ?? false);
                        $numPets = (int)($room['num_pets'] ?? 0);

                        if (!$agreed) {
                            return response()->json([
                                'success' => false,
                                'message' => "Pet policy agreement is required for room ID {$roomId}.",
                            ], 422);
                        }

                        if ($numPets < 1 || $numPets > 2) {
                            return response()->json([
                                'success' => false,
                                'message' => "Please set number of pets (1-2) for room ID {$roomId}.",
                            ], 422);
                        }
                    }
                }

                // Read current booking session
                $booking = session('booking', [
                    'checkin'         => null,
                    'checkout'        => null,
                    'accommodations'  => [],
                    'booking_type'    => 'staycation',
                    'timestamp'       => now()->timestamp,
                    'completed_steps' => [],
                    'current_step'    => 1,
                ]);

                // Ensure booking type
                $booking['booking_type'] = 'staycation';

                /**
                 * Normalize to a clean "accommodations" array for downstream steps:
                 * - Key by room_id so easy update/remove
                 */
                $normalized = [];

                foreach ($selectedRooms as $roomId => $room) {
                    $roomId = (int)$roomId;

                    $normalized[$roomId] = [
                        'room_id'        => $roomId,
                        'adults'         => (int)($room['adults'] ?? 1),
                        'children'       => (int)($room['children'] ?? 0),
                        'extra_mattress' => (int)($room['extra_mattress'] ?? 0),
                        'pets'           => ($room['pets'] ?? 'no'),
                        'pet_agreement'  => (bool)($room['pet_agreement'] ?? false),
                        'num_pets'       => (int)($room['num_pets'] ?? 0),
                    ];
                }

                $booking['accommodations'] = $normalized;

                // mark step1 complete
                $completed = $booking['completed_steps'] ?? [];
                if (!in_array(1, $completed)) $completed[] = 1;
                $booking['completed_steps'] = $completed;
                $booking['current_step'] = 2;

                session(['booking' => $booking]);

                // If your JS expects JSON success (as in the merged blade), return JSON:
                if (!empty($data['proceed_to_activities'])) {
                    return response()->json([
                        'success' => true,
                    ]);
                }

                // Otherwise fallback
                return response()->json([
                    'success' => true,
                    'message' => 'Selection saved.',
                ]);
            }

            $request->validate(ValidationService::staycationPostRules());

            $booking = session('booking', []);

            if ($request->has('check_in') && $request->has('check_out')) {
                $booking['checkin'] = $request->check_in;
                $booking['checkout'] = $request->check_out;
                $booking['booking_type'] = 'staycation';
                $booking['completed_steps'] = [1];
                $booking['current_step'] = 1;
                $booking['timestamp'] = now()->timestamp;
                session(['booking' => $booking]);
                return redirect()->route('booking.staycationAvailability');
            }

            if ($request->has('accommodation_type')) {
                $booking['accommodation_type'] = $request->accommodation_type;
                if ($request->has('selected_room_id')) {
                    $booking['selected_room_id'] = $request->selected_room_id;
                }
                $booking['completed_steps'] = array_unique(
                    array_merge($booking['completed_steps'] ?? [], [1.5])
                );
                session(['booking' => $booking]);
                return redirect()->route('booking.staycation');
            }

            if ($request->has('selected_room_id') && $request->input('proceed_to_activities')) {
                // Handle room selection from availability page
                $selectedStaycation = Staycation::with('texts')->find($request->selected_room_id);
                if ($selectedStaycation) {
                    $booking['accommodations'] = [
                        [
                            'staycation' => $selectedStaycation,
                            'guests' => 2, // Default guests, can be modified later
                            'has_breakfast' => false,
                            'pet_agreement' => false,
                            'num_pets' => 0
                        ]
                    ];
                    $booking['completed_steps'] = array_unique(
                        array_merge($booking['completed_steps'] ?? [], [2])
                    );
                    $booking['current_step'] = 3; // Mark availability as completed
                }
                session(['booking' => $booking]);
                return response()->json(['success' => true]);
            }
        }

        /**
         * 🔥 IMPORTANT PART
         * Eager-load variants so the modal can be DB-driven
         */
        // $accommodations = StaycationAccommodation::with([
        //     'accommodationTexts', // texts for the accommodation itself
        //     'variants.texts',     // texts for the variants (via staycation)
        // ])->get();

        $accommodations = StaycationAccommodation::with([
            'accommodationTexts',
            'variants', // load variants (staycations)
            'variants.texts', // load texts for the variants
            'variants.swiperImages', // load images
        ])->get();

        $booking = session('booking', []);
        $checkinDate = $booking['checkin'] ?? null;
        $checkoutDate = $booking['checkout'] ?? null;

        $availability = [];
        if ($checkinDate && $checkoutDate) {
            $staycationIds = $accommodations->pluck('id')->toArray();
            $availability = $this->checkAvailabilityInternal(
                $checkinDate,
                $checkoutDate,
                $staycationIds
            );
        }

        // Pre-select accommodation if package is provided
        if ($request->has('package')) {
            $packageName = urldecode($request->get('package'));
            $selectedAccommodation = $accommodations->firstWhere('name', $packageName);

            if ($selectedAccommodation) {
                $booking['accommodations'][0] = [
                    'staycation' => $selectedAccommodation,
                    'guests' => $selectedAccommodation->adults ?? 2,
                ];
                session(['booking' => $booking]);
            }
        }

        $accommodationType = $booking['accommodation_type'] ?? null;

        return view('booking.staycation', compact('accommodations', 'availability', 'accommodationType'));
    }

    public function staycationAvailability()
    {
        $booking = session('booking', []);

        // If no dates selected, use default dates for demo purposes
        if (empty($booking['checkin']) || empty($booking['checkout'])) {
            $checkin = now()->format('Y-m-d');
            $checkout = now()->addDays(2)->format('Y-m-d');
        } else {
            $checkin = $booking['checkin'];
            $checkout = $booking['checkout'];
        }

        // Get all staycations from admin panel
        $staycations = Staycation::with([
            'texts',
            'swiperImages'
        ])->get();

        // Commented out dummy data creation for production
        // if ($staycations->isEmpty()) {
        //     $dummyStaycations = collect([
        //         (object) [
        //             'id' => 1,
        //             'name' => 'Standard Glamping',
        //             'texts' => collect([(object)['description' => 'Experience luxury camping with our standard glamping tents.']]),
        //             'swiperImages' => collect([(object)['image_path' => asset('assets/images/carousel/Glamping-1.jpg')]]),
        //             'inventories' => collect([])
        //         ],
        //         (object) [
        //             'id' => 2,
        //             'name' => 'Premium Glamping',
        //             'texts' => collect([(object)['description' => 'Indulge in premium glamping with enhanced amenities.']]),
        //             'swiperImages' => collect([(object)['image_path' => asset('assets/images/carousel/Glamping-1.jpg')]]),
        //             'inventories' => collect([])
        //         ],
        //         (object) [
        //             'id' => 3,
        //             'name' => 'Dome Tent',
        //             'texts' => collect([(object)['description' => 'Cozy dome tents perfect for families.']]),
        //             'swiperImages' => collect([(object)['image_path' => asset('assets/images/carousel/Car-camping3.jpg')]]),
        //             'inventories' => collect([])
        //         ]
        //     ]);
        //     $staycations = $dummyStaycations;
        // }

        // Calculate availability and details for each staycation
        $availability = [];
        $roomDetails = [];

        foreach ($staycations as $staycation) {
            $totalNights = \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout));
            $isAvailable = $staycation->quantity > 0;

            $availability[$staycation->id] = [
                'available' => $isAvailable,
                'partially_available' => false,
                'available_nights' => $totalNights,
                'total_nights' => $totalNights,
                'inventory_details' => [],
                'total_quantity' => $staycation->quantity,
                'min_quantity' => $staycation->quantity,
                'has_inventory' => false
            ];

            // Prepare room details for display
            $description = $staycation->texts->first()->description ?? 'Experience the beauty of nature with our premium accommodation options.';
            $image = $staycation->image ?? asset('assets/images/carousel/Glamping-1.jpg');

            $name = strtolower($staycation->name);
            if (strpos($name, 'glamping') !== false) {
                $type = 'glamping';
            } elseif (strpos($name, 'tent') !== false || strpos($name, 'camping') !== false) {
                $type = 'camping';
            } else {
                $type = 'camping'; // default
            }

            $roomDetails[$staycation->id] = [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'type' => $type,
                'description' => $description,
                'image' => $image,
                'status' => $isAvailable ? 'available' : 'unavailable',
                'available_quantity' => $staycation->quantity,
                'total_quantity' => $staycation->quantity,
                'price' => $this->calculatePrice($staycation, $checkin),
                'price_configs' => $staycation->price_configs ?? []
            ];
        }

        return view('booking.staycation-availability', compact('staycations', 'availability', 'roomDetails', 'checkin', 'checkout'));
    }

    public function getStaycationAvailabilityApi(Request $request)
    {
        $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'type' => 'nullable|string|in:glamping,camping'
        ]);

        $checkin = $request->checkin;
        $checkout = $request->checkout;
        $type = $request->type;

        // Get staycations
        $query = Staycation::with([
            'texts',
            'swiperImages'
        ]);

        if ($type) {
            if ($type === 'glamping') {
                $query->where('name', 'like', '%Glamping%');
            } else {
                $query->where(function($q) {
                    $q->where('name', 'like', '%Camping%')
                      ->orWhere('name', 'like', '%Tent%')
                      ->orWhere('name', 'like', '%BYOT%')
                      ->orWhere('name', 'like', '%Car%');
                });
            }
        }

        $staycations = $query->get();

        // Calculate availability
        $availability = [];
        foreach ($staycations as $staycation) {
            $totalNights = \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout));
            $isAvailable = $staycation->quantity > 0;

            $availability[$staycation->id] = [
                'available' => $isAvailable,
                'available_nights' => $totalNights,
                'total_nights' => $totalNights,
                'inventory_details' => [],
                'has_inventory' => false
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'staycations' => $staycations->values(),
                'availability' => $availability,
                'checkin' => $checkin,
                'checkout' => $checkout
            ]
        ]);
    }


    private function checkAvailabilityInternal($checkin, $checkout, $staycationIds)
    {
        // For now, assume all staycations are available since no booking system yet
        $available = [];
        foreach ($staycationIds as $id) {
            $available[$id] = [
                'available' => true,
                'text' => 'Available for booking'
            ];
        }
        return $available;
    }

    public function selectDates(Request $request)
    {
        $request->validate(ValidationService::staycationDatesRules());

        $booking = session('booking', []);
        $booking['checkin'] = $request->checkin;
        $booking['checkout'] = $request->checkout;
        $booking['completed_steps'] = [1]; // Mark step 1 as completed
        session(['booking' => $booking]);

        return response()->json([
            'success' => true,
            'message' => 'Dates selected successfully.',
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $request->validate(ValidationService::availabilityCheckRules());

        $checkin = $request->checkin;
        $checkout = $request->checkout;
        $staycationIds = $request->staycation_ids;

        $available = $this->checkAvailabilityInternal($checkin, $checkout, $staycationIds);

        return response()->json([
            'available' => $available,
            'message' => 'Availability checked successfully.',
        ]);
    }

    public function dayvisit(Request $request)
    {
        // Handle POST request from landing page with selected date
        if ($request->isMethod('post')) {
            $selectedDate = $request->input('check_in');
            if ($selectedDate) {
                // Store the selected date and redirect to booking form
                session(['selected_dayvisit_date' => $selectedDate]);
                return redirect()->route('booking.dayvisit');
            }
        }

        // For GET request, show day visit form with auto-selected date
        $type = 'day-visit';
        $package = 'Day Visit Package with Free Drinks (500 per person)';
        $price = 500;

        // Check if date was pre-selected from landing page
        $selectedDate = session('selected_dayvisit_date');
        if (!$selectedDate) {
            // Auto-select today's date or next available date
            $today = now()->format('Y-m-d');
            $selectedDate = $today;

            $inventory = $this->inventoryService->getAllInventory();

            // If today is not available or has low availability, try next few days
            if (!isset($inventory['day-visit'][$today]) || $inventory['day-visit'][$today] <= 0) {
                for ($i = 1; $i <= 7; $i++) {
                    $nextDate = now()->addDays($i)->format('Y-m-d');
                    if (isset($inventory['day-visit'][$nextDate]) && $inventory['day-visit'][$nextDate] > 0) {
                        $selectedDate = $nextDate;
                        break;
                    }
                }
            }
        }

        return view('booking.dayvisit', compact('type', 'package', 'price', 'selectedDate'));
    }

    public function storeDayvisit(Request $request)
    {
        $validated = $request->validate(ValidationService::dayvisitBookingRules());

        // Use session date if no booking_date provided
        $bookingDate = $validated['booking_date'] ?? session('selected_dayvisit_date');
        if (!$bookingDate) {
            return redirect()->route('booking.dayvisit')->with('error', 'Please select a booking date.');
        }

        $validated['booking_date'] = $bookingDate;
        $validated['type'] = 'day-visit';
        $validated['package'] = 'Day Visit (500 per person)';
        $validated['price'] = 500 * $validated['guests'];

        $date = $validated['booking_date'];

        // Check availability
        if (!$this->inventoryService->isAvailable('day-visit', $date)) {
            return redirect()->back()->with('error', 'No availability for the selected date.');
        }

        // Deduct inventory
        $this->inventoryService->reduceInventory('day-visit', $date);

        // Store booking data in session
        session(['dayvisit_booking_data' => $validated]);

        // Clear the selected date from session
        session()->forget('selected_dayvisit_date');

        // Redirect to activities
        return redirect()->route('booking.dayvisitActivities');
    }

    public function dayvisitActivities()
    {
        return $this->getBookingActivities('dayvisit');
    }

    public function getDayvisitActivitiesApi()
    {
        $activities = $this->bookingService->getActivitiesData();
        return response()->json($activities);
    }


    public function storeDayvisitActivities(Request $request)
    {
        return $this->storeBookingActivities($request, 'dayvisit', ValidationService::activitiesRules(), 'dayvisit_selected_activities');
    }

    public function dayvisitPayment()
    {
        return $this->getBookingPayment('dayvisit');
    }

    private function calculatePrice($staycation, $date, $guests = 2)
    {
        // Use staycation's price_configs from admin panel
        $priceConfigs = $staycation->price_configs ?? [];

        if (count($priceConfigs) > 0) {
            // Determine day type and season type for the date
            $dayType = $this->determineDayTypeForDate($date);
            $seasonType = $this->determineSeasonTypeForDate($date);

            // Map season types to rate keys
            $rateKeyMap = [
                'non-peak' => 'nonPeak',
                'peak' => 'peak',
                'super-peak' => 'superPeak'
            ];

            $rateKey = $rateKeyMap[$seasonType] ?? 'nonPeak';

            // Find applicable price config for this date and day type
            foreach ($priceConfigs as $config) {
                $applies = false;

                if (($config['type'] ?? 'day') === 'day') {
                    // Check if day matches
                    $configDays = $config['days'] ?? [];
                    $dateDay = \Carbon\Carbon::parse($date)->format('D'); // Mon, Tue, etc.
                    if (in_array($dateDay, $configDays)) {
                        $applies = true;
                    }
                } elseif (($config['type'] ?? 'day') === 'date') {
                    // Check specific dates
                    $configDates = $config['dates'] ?? '';
                    if (str_contains($configDates, $date) || $configDates === $date) {
                        $applies = true;
                    }
                }

                if ($applies) {
                    $rates = $config['rates'] ?? [];
                    $baseRate = $rates[$rateKey] ?? 0;

                    // For staycations, price is fixed regardless of guests (up to max capacity)
                    return $baseRate;
                }
            }

            // If no specific config applies, use first config's rate
            $config = $priceConfigs[0];
            $rates = $config['rates'] ?? [];
            $baseRate = $rates[$rateKey] ?? 0;

            // Apply guest-based pricing
            $baseGuests = 2;
            $extraGuestRate = 500;

            if ($guests > $baseGuests) {
                $extraGuests = $guests - $baseGuests;
                $baseRate += $extraGuests * $extraGuestRate;
            }

            return $baseRate;
        }

        // Fallback to hardcoded pricing if no price configs
        $baseRate = $staycation->name && strpos(strtolower($staycation->name), 'glamping') !== false ? 5500 : 3000;

        // Apply guest-based pricing to fallback
        $baseGuests = 2;
        $extraGuestRate = 500;

        if ($guests > $baseGuests) {
            $extraGuests = $guests - $baseGuests;
            $baseRate += $extraGuests * $extraGuestRate;
        }

        return $baseRate;
    }

    /**
     * Determine day type for a specific date (Holiday > Weekend > Weekday)
     */
    private function determineDayTypeForDate($date)
    {
        $carbonDate = \Carbon\Carbon::parse($date);
        $dateStr = $carbonDate->format('Y-m-d');
        $dayOfWeek = (int)$carbonDate->dayOfWeek;

        // Check if it's a declared holiday
        $isHoliday = Holiday::whereDate('date', $dateStr)->exists();
        if ($isHoliday) {
            return 'holiday';
        }

        // Check if it's a weekend
        $weekendDayIds = DayTypeDay::whereHas('dayType', fn($q) => $q->where('name', 'Weekend'))
            ->pluck('weekday')
            ->toArray();

        if (in_array($dayOfWeek, $weekendDayIds)) {
            return 'weekend';
        }

        return 'weekday';
    }

    /**
     * Determine season type for a specific date (Super-Peak > Peak > Non-Peak)
     */
    private function determineSeasonTypeForDate($date)
    {
        $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');

        // Get all peak periods that include this date
        $peakPeriods = PeakPeriod::where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->with('peakType')
            ->get();

        if ($peakPeriods->isEmpty()) {
            return 'non-peak';
        }

        // Find the highest priority peak type
        $highestPriority = -999;
        $selectedType = 'non-peak';

        foreach ($peakPeriods as $period) {
            $priority = $period->peakType->priority ?? 0;
            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $selectedType = $period->peakType->name;
            }
        }

        return strtolower($selectedType);
    }

    /**
     * AJAX endpoint to calculate dynamic pricing for staycations
     */
    public function getDynamicPrice(Request $request)
    {
        $request->validate([
            'unit_type' => 'required|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:50'
        ]);

        $unitType = $request->unit_type;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $guests = $request->guests;

        // Extract staycation ID from unit_type (format: staycation-{id})
        if (!preg_match('/^staycation-(\d+)$/', $unitType, $matches)) {
            return response()->json(['success' => false, 'message' => 'Invalid unit type']);
        }

        $staycationId = $matches[1];
        $staycation = Staycation::find($staycationId);

        if (!$staycation) {
            return response()->json(['success' => false, 'message' => 'Staycation not found']);
        }

        // Calculate total price for the stay
        $totalPrice = 0;
        $checkinDate = \Carbon\Carbon::parse($checkIn);
        $checkoutDate = \Carbon\Carbon::parse($checkOut);

        $current = $checkinDate->copy();
        while ($current->lt($checkoutDate)) {
            $dateStr = $current->format('Y-m-d');
            $price = $this->calculatePrice($staycation, $dateStr, $guests);
            $totalPrice += $price;
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'total_price' => $totalPrice,
            'nights' => $checkinDate->diffInDays($checkoutDate),
            'guests' => $guests
        ]);
    }






}
