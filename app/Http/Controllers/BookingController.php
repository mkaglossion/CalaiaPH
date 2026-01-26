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
use App\Models\Food;
use App\Models\Holiday;
use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakPeriod;
use App\Models\PeakType;
use App\Models\StaycationInventory;
use App\Services\PricingService;
use App\Models\BookingSetting;


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

    /**
     * Get booking settings (pricing) as JSON
     * Available publicly for frontend price calculations
     */
    public function getBookingSettings()
    {
        $settings = BookingSetting::where('category', 'pricing')->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = (float)$setting->value;
        }

        return response()->json($result);
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
            // Fetch staycations from the database
            $staycations = Staycation::all()->keyBy('id'); // Key by ID for proper lookup

        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete accommodation selection first.');
        }

        $activities = $this->bookingService->getActivitiesData();
        
        // Get selected foods from session
        $selectedFoods = $booking['selected_foods'] ?? session('selected_foods', []);
        
        // Fetch food details if we have selected foods
        $foods = [];
        if (!empty($selectedFoods)) {
            $foodIds = array_column($selectedFoods, 'id');
            $foods = \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id');
        }

            return view('booking.staycation-activities', compact('booking', 'activities', 'staycations', 'selectedFoods', 'foods'));
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
            $accommodations = $booking['accommodations'];
            $accommodationTotal = $this->calculateStaycationTotal($accommodations, $checkinDate, $checkoutDate);
            $booking['accommodations'] = $accommodations;
        }

        $activitiesTotal = collect($request->activities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        // Calculate food total if foods were selected
        $foodTotal = 0;
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $foodData) {
                $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1);
            }
        }

        // Store final totals in session
        $booking['final_totals'] = [
            'accommodation_total' => $accommodationTotal,
            'activities_total' => $activitiesTotal,
            'food_total' => $foodTotal,
            'grand_total' => $accommodationTotal + $activitiesTotal + $foodTotal
        ];

        $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [3]));
        $booking['current_step'] = 4;
        session(['booking' => $booking]);

        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    /**
     * Calculate total price for staycation accommodations
     */
    private function calculateStaycationTotal(array &$accommodations, string $checkinDate, string $checkoutDate): float
    {
        $total = 0;

        foreach ($accommodations as &$acc) {
            if (empty($acc['room_id'])) continue;

            $staycation = Staycation::find($acc['room_id']);
            if (!$staycation || empty($staycation->price_configs)) continue;

                // Recalculate prices properly
                $roomTotal = $this->calculateStaycationRoomPrice($staycation, $checkinDate, $checkoutDate, $acc);
            
                // Update accommodation prices in session
                $nights = \Carbon\Carbon::parse($checkinDate)->diffInDays(\Carbon\Carbon::parse($checkoutDate));
                $roomName = strtolower($staycation->name);
                $isBYOT = strpos($roomName, 'byot') !== false;
                $isCarCamping = strpos($roomName, 'car camping') !== false;
            
                if (!($isBYOT || $isCarCamping)) {
                    $extraMattresses = $acc['extra_mattress'] ?? 0;
                    $mattressPrice = $this->calculateMattressFee($extraMattresses, $nights);
                    $petPrice = ($acc['pet_agreement'] ?? false) ? $this->calculatePetFee($acc['num_pets'] ?? 0) : 0;
                    $basePrice = $roomTotal - $mattressPrice - $petPrice;
                
                    $acc['base_price'] = $basePrice;
                    $acc['mattress_price'] = $mattressPrice;
                    $acc['pet_price'] = $petPrice;
                    $acc['total_price'] = $roomTotal;
                } else {
                    $acc['base_price'] = $roomTotal;
                    $acc['mattress_price'] = 0;
                    $acc['pet_price'] = 0;
                    $acc['total_price'] = $roomTotal;
                }
            
                $total += $roomTotal;
        }

            unset($acc);

        return $total;
    }

    /**
     * Calculate price for a single staycation room
     */
    private function calculateStaycationRoomPrice(Staycation $staycation, string $checkinDate, string $checkoutDate, array &$acc): float
    {
        $total = 0;
        $accommodationTotal = 0;
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);
        $nights = $checkin->diffInDays($checkout);

        // Detect if this is a BYOT or Car Camping room
        $roomName = strtolower($staycation->name);
        $isBYOT = strpos($roomName, 'byot') !== false || strpos($roomName, 'bring your own tent') !== false;
        $isCarCamping = strpos($roomName, 'car camping') !== false;

        // Get holiday dates
        $holidayDates = Holiday::whereBetween('date', [$checkinDate, $checkout->copy()->subDay()->format('Y-m-d')])
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Get weekend day IDs
        $weekendDayIds = DayTypeDay::whereHas('dayType', fn($q) => $q->where('name', 'Weekend'))
            ->pluck('weekday')
            ->toArray();

        // For BYOT/Car Camping, calculate base price per night per unit (quantity)
        if ($isBYOT || $isCarCamping) {
            $quantity = $acc['extra_mattress'] ?? 1;
            
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
                    // For BYOT/Car Camping: multiply rate by quantity
                    $total += $rate * $quantity;
                }

                $current->addDay();
            }
            
            // No mattress or pet fees for BYOT/Car Camping
            $accommodationTotal = $total;
        } else {
            // Regular rooms: calculate nightly rates and add additional charges
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
            
            // Store the base accommodation price (before extras)
            $accommodationTotal = $total;

            // Add extra mattress fee
            $extraMattresses = $acc['extra_mattress'] ?? 0;
            $total += $this->calculateMattressFee($extraMattresses, $nights);

            // Add pet fees
            if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
                $total += $this->calculatePetFee($acc['num_pets']);
            }
        }
        
        // Store accommodation price in acc for later use
        $acc['accommodation_price'] = $accommodationTotal;

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

        // Use the accommodation total already stored in session (don't recalculate to avoid price discrepancies)
        $checkinDate = $booking['checkin'];
        $checkoutDate = $booking['checkout'];
        $total = 0;
        foreach ($booking['accommodations'] ?? [] as $acc) {
            if (!empty($acc)) {
                $total += $acc['total_price'] ?? 0;
            }
        }
        
        // Update booking with fresh totals
        $activitiesTotal = collect($booking['selected_activities'] ?? [])->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });
        
        // Calculate nights for food pricing
        $nights = \Carbon\Carbon::parse($checkinDate)->diffInDays(\Carbon\Carbon::parse($checkoutDate));

        $foodTotal = 0;
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $foodData) {
                $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1) * $nights;
            }
        }
        
        $booking['final_totals'] = [
            'accommodation_total' => $total,
            'activities_total' => $activitiesTotal,
            'food_total' => $foodTotal,
            'grand_total' => $total + $activitiesTotal + $foodTotal
        ];
        
        session(['booking' => $booking]);

        // Get selected foods from current booking session only (not old session data)
        $selectedFoods = $booking['selected_foods'] ?? [];

        // Fetch food details if we have selected foods
        $foods = [];
        if (!empty($selectedFoods)) {
            $foodIds = array_column($selectedFoods, 'id');
            $foods = \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id');
        }

        // Fetch staycations for room names
        $staycations = \App\Models\Staycation::all()->keyBy('id');

        return view('booking.staycation-payment', compact('booking', 'selectedFoods', 'foods', 'staycations'));
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
            // Fetch food and staycation models for confirmation display
            $foods = Food::all();
            $staycations = Staycation::all()->keyBy('id');
            
            $data = [
                'bookingData' => $staycationBooking,
                'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
                'selectedFoods' => $staycationBooking['selected_foods'] ?? [],
                'foods' => $foods,
                'staycations' => $staycations,
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

        // Get booking data from session first, then use posted data as backup
        $staycationBooking = session('booking', []);
        
        // If booking data was posted, merge it with session data to ensure we have everything
        if ($request->has('accommodations')) {
            $postedAccommodations = json_decode($request->input('accommodations', '[]'), true);
            if (!empty($postedAccommodations)) {
                $staycationBooking['accommodations'] = $postedAccommodations;
            }
        }
        
        if ($request->has('selected_foods')) {
            $postedFoods = json_decode($request->input('selected_foods', '[]'), true);
            if (!empty($postedFoods)) {
                $staycationBooking['selected_foods'] = $postedFoods;
            }
        }
        
        if ($request->has('selected_activities')) {
            $postedActivities = json_decode($request->input('selected_activities', '[]'), true);
            if (!empty($postedActivities)) {
                $staycationBooking['selected_activities'] = $postedActivities;
            }
        }
        
        if ($request->has('checkin')) {
            $staycationBooking['checkin'] = $request->input('checkin');
        }
        
        if ($request->has('checkout')) {
            $staycationBooking['checkout'] = $request->input('checkout');
        }

        // Get other booking data
        $bookingData = session('booking_data');
        $dayvisitBooking = session('dayvisit_booking_data');

        // Check if we have any booking data
        if (!$bookingData && empty($staycationBooking) && !$dayvisitBooking) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Define payment processors for each booking type
        $paymentProcessors = [
            [
                'condition' => !empty($staycationBooking) && isset($staycationBooking['booking_type']) && $staycationBooking['booking_type'] === 'staycation',
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
        // Get inventory data from StaycationInventory (similar to BookingCalendarController)
        $start = \Carbon\Carbon::today();
        $end = (clone $start)->addMonths(6);

        $inventory = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(\Carbon\Carbon::today())) {
                $inventory[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available quantity for this date from inventory table
            $totalAvailable = \App\Models\StaycationInventory::where('date', $key)
                ->sum('available_quantity');

            // If no inventory records exist for this date, fall back to staycation quantity
            if ($totalAvailable == 0) {
                $totalAvailable = \App\Models\Staycation::sum('quantity');
            }

            $inventory[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];
        }

        // Get total quantity for fallback
        $totalQuantity = \App\Models\Staycation::sum('quantity');

        return view('booking.widget', [
            'inventory'     => $inventory,
            'totalQuantity' => $totalQuantity,
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
                    'selected_rooms.*.accommodation_price' => ['nullable', 'numeric', 'min:0'],
                    'selected_rooms.*.adults' => ['required', 'integer', 'min:1', 'max:20'],
                    'selected_rooms.*.children' => ['nullable', 'integer', 'min:0', 'max:20'],
                    'selected_rooms.*.extra_mattress' => ['nullable', 'integer', 'min:0', 'max:100'], // Increased to allow BYOT/Car Camping quantities
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
                    
                    // Fetch staycation to get pricing info
                    $staycation = Staycation::find($roomId);
                    
                    // Get checkin/checkout dates from booking session
                    $checkinDate = $booking['checkin'] ?? null;
                    $checkoutDate = $booking['checkout'] ?? null;
                    
                    // If dates not in booking, try to get from request
                    if (!$checkinDate && $request->has('checkin')) {
                        $checkinDate = $request->input('checkin');
                    }
                    if (!$checkoutDate && $request->has('checkout')) {
                        $checkoutDate = $request->input('checkout');
                    }
                    
                    // Calculate nights - make sure we have valid dates
                    $nights = 1; // Default minimum
                    if ($checkinDate && $checkoutDate) {
                        $nights = max(1, \Carbon\Carbon::parse($checkinDate)->diffInDays(\Carbon\Carbon::parse($checkoutDate)));
                    }
                    
                    // Check if BYOT or Car Camping
                    $roomNameLower = strtolower($staycation->name ?? '');
                    // Check for BYOT first (prioritize BYOT over car camping)
                    $isBYOT = str_contains($roomNameLower, 'byot') ||
                             str_contains($roomNameLower, 'bring your own tent') ||
                             str_contains($roomNameLower, 'bring your own');
                    // Check for car camping only if not BYOT
                    $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');

                    // Use the accommodation_price sent from the frontend if available
                    // This ensures we display exactly what the customer selected
                    $accommodationPrice = (float)($room['accommodation_price'] ?? 0);
                    
                    // If no price from frontend, calculate server-side
                    if ($accommodationPrice <= 0) {
                        $totalPrice = $this->calculateStaycationRoomPrice($staycation, $checkinDate, $checkoutDate, $room);
                        $accommodationPrice = $totalPrice; // Use calculated total as accommodation price
                    } else {
                        // Frontend sent the accommodation price, calculate total including extras
                        $totalPrice = $accommodationPrice;
                    }

                    // Calculate costs for display
                    $numPets = (int)($room['num_pets'] ?? 0);
                    $adults = (int)($room['adults'] ?? 1);

                    if ($isBYOT || $isCarCamping) {
                        // For BYOT/Car Camping: quantity is from extra_mattress field
                        $quantity = (int)($room['extra_mattress'] ?? 1);
                        $roomPrice = $totalPrice; // Total price is already calculated
                        $mattressPrice = 0; // No separate mattress charge for BYOT/Car Camping
                    } else {
                        // For regular rooms: quantity is always 1
                        $quantity = 1;
                        // For regular rooms: calculate components for display
                        $extraMattress = (int)($room['extra_mattress'] ?? 0);
                        $mattressPrice = $this->calculateMattressFee($extraMattress, $nights);
                        $roomPrice = $accommodationPrice; // Use the accommodation price we received/calculated
                        $totalPrice = $roomPrice + $mattressPrice; // Add mattress to total
                    }
                    
                    // Pet charge applies to all room types (one-time, not per night)
                    $petPrice = $this->calculatePetFee($numPets);
                    if (!($isBYOT || $isCarCamping)) {
                        // Only add pet price for non-BYOT/Car Camping rooms
                        $totalPrice += $petPrice;
                    }

                    $normalized[$roomId] = [
                        'room_id'        => $roomId,
                        'accommodation_id' => $roomId,
                        'accommodation_price' => $accommodationPrice, // Store the base accommodation price
                        'adults'         => $adults,
                        'children'       => (int)($room['children'] ?? 0),
                        'extra_mattress' => (int)($room['extra_mattress'] ?? 0),
                        'quantity'       => $quantity, // Add quantity for inventory reduction
                        'pets'           => ($room['pets'] ?? 'no'),
                        'pet_agreement'  => (bool)($room['pet_agreement'] ?? false),
                        'num_pets'       => $numPets,
                        'base_price'     => $roomPrice,
                        'mattress_price' => $mattressPrice,
                        'pet_price'      => $petPrice,
                        'total_price'    => $totalPrice,
                    ];
                }

                $booking['accommodations'] = $normalized;

                // Clear any previous food and activities selections for a fresh booking
                $booking['selected_foods'] = [];
                $booking['selected_activities'] = [];
                unset($booking['final_totals']); // Clear old totals

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

            // Handle selected_foods from the food selection step
            if ($request->has('selected_foods')) {
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'selected_foods' => ['required', 'array', 'min:1'],
                    'selected_foods.*.id' => ['required'],
                    'selected_foods.*.name' => ['required', 'string'],
                    'selected_foods.*.category' => ['nullable', 'string'],
                    'selected_foods.*.price' => ['required', 'numeric', 'min:0'],
                    'selected_foods.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
                    'selected_foods.*.notes' => ['nullable', 'string', 'max:500'],
                    'proceed_to_activities' => ['nullable', 'boolean'],
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid food selection data.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $booking = session('booking', [
                    'checkin' => null,
                    'checkout' => null,
                    'accommodations' => [],
                    'booking_type' => 'staycation',
                    'timestamp' => now()->timestamp,
                    'completed_steps' => [],
                    'current_step' => 1,
                ]);

                $booking['booking_type'] = 'staycation';
                $booking['selected_foods'] = $data['selected_foods'];
                $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [2]));
                $booking['current_step'] = 3;

                session(['booking' => $booking]);

                return response()->json([
                    'success' => true,
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
                return redirect()->route('booking.staycationAvailability');
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

        // For GET requests, delegate to the availability page builder
        return $this->staycationAvailability();
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

        $checkinCarbon = \Carbon\Carbon::parse($checkin);
        $checkoutCarbon = \Carbon\Carbon::parse($checkout);

        foreach ($staycations as $staycation) {
            $totalNights = $checkinCarbon->diffInDays($checkoutCarbon);

            // Fetch inventory for this staycation in the selected range
            $inventories = StaycationInventory::where('staycation_id', $staycation->id)
                ->whereBetween('date', [$checkinCarbon->format('Y-m-d'), $checkoutCarbon->copy()->subDay()->format('Y-m-d')])
                ->get(['date', 'available_quantity'])
                ->keyBy(function ($i) { return \Carbon\Carbon::parse($i->date)->format('Y-m-d'); });

            $inventoryDetails = [];
            $current = $checkinCarbon->copy();
            $minOverRange = null;
            $availableNights = 0;

            while ($current->lt($checkoutCarbon)) {
                $dateStr = $current->format('Y-m-d');
                // Get from inventory table, if not exists use 0 (not available)
                $qty = isset($inventories[$dateStr]) ? (int) $inventories[$dateStr]->available_quantity : 0;
                $inventoryDetails[$dateStr] = [
                    'min_quantity' => $qty,
                    'is_available' => $qty > 0,
                ];
                $minOverRange = is_null($minOverRange) ? $qty : min($minOverRange, $qty);
                if ($qty > 0) $availableNights++;
                $current->addDay();
            }

            $minOverRange = $minOverRange ?? 0;
            $isAvailable = $minOverRange > 0;

            // Dynamic status based on threshold (30% of base quantity)
            $baseQty = max(0, (int)$staycation->quantity);
            $threshold = max(1, (int) floor($baseQty * 0.3));
            $status = 'unavailable';
            if ($minOverRange === 0) {
                $status = 'unavailable';
            } elseif ($minOverRange <= $threshold) {
                $status = 'limited';
            } else {
                $status = 'available';
            }

            $availability[$staycation->id] = [
                'available' => $isAvailable,
                'partially_available' => $isAvailable && $availableNights < $totalNights,
                'available_nights' => $availableNights,
                'total_nights' => $totalNights,
                'inventory_details' => $inventoryDetails,
                'total_quantity' => $baseQty,
                'min_quantity' => $minOverRange,
                'has_inventory' => true
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

            // Calculate the proper total price for the entire stay (same as booking processing)
            $name = strtolower($staycation->name);
            if (strpos($name, 'byot') !== false || strpos($name, 'car camping') !== false) {
                $mockAcc = ['extra_mattress' => 1];
            } else {
                $mockAcc = ['extra_mattress' => 0];
            }
            $totalPrice = $this->calculateStaycationRoomPrice($staycation, $checkin, $checkout, $mockAcc);

            $roomDetails[$staycation->id] = [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'type' => $type,
                'description' => $description,
                'image' => $image,
                'status' => $status,
                'available_quantity' => $minOverRange,
                'total_quantity' => $baseQty,
                'price' => $totalPrice, // Total price for the entire stay
                'price_configs' => $staycation->price_configs ?? []
            ];
        }

        return view('booking.staycation-availability', compact('staycations', 'availability', 'roomDetails', 'checkin', 'checkout'));
    }

    public function staycationFood()
    {
        $booking = session('booking', []);

        // Ensure rooms were selected first
        if (empty($booking['accommodations'])) {
            return redirect()->route('booking.staycationAvailability')
                ->with('error', 'Please select your room(s) first.');
        }

        // Clear any previous food selections to start fresh
        session()->forget('selected_foods');
        $booking['selected_foods'] = [];

        // Pull food items from database with images and texts, sorted by category then name
        $foodMenus = Food::with(['swiperImages', 'texts'])
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Fetch staycation data for display in summary (array keys are room IDs)
        $accommodationIds = array_keys($booking['accommodations'] ?? []);
        $staycations = Staycation::whereIn('id', $accommodationIds)->get();

        return view('booking.staycation-food', compact('booking', 'foodMenus', 'staycations'));
    }

    public function saveFoodSelection(Request $request)
    {
        $validated = $request->validate([
            'selected_foods' => 'required|array',
            'selected_foods.*.id' => 'required|integer',
            'selected_foods.*.name' => 'required|string',
            'selected_foods.*.category' => 'required|string',
            'selected_foods.*.price' => 'required|numeric',
            'selected_foods.*.quantity' => 'required|integer|min:1',
            'selected_foods.*.notes' => 'nullable|string'
        ]);

        // Store selected foods in session (legacy key)
        session(['selected_foods' => $validated['selected_foods']]);

        // Also persist inside booking session so next steps have rooms + foods together
        $booking = session('booking', [
            'checkin' => null,
            'checkout' => null,
            'accommodations' => [],
            'booking_type' => 'staycation',
            'timestamp' => now()->timestamp,
            'completed_steps' => [],
            'current_step' => 1,
        ]);

        $booking['booking_type'] = 'staycation';
        $booking['selected_foods'] = $validated['selected_foods'];

        // Calculate nights for food pricing
        $nights = \Carbon\Carbon::parse($booking['checkin'])->diffInDays(\Carbon\Carbon::parse($booking['checkout']));

        // Calculate food total from selected foods
        $foodTotal = 0;
        foreach ($validated['selected_foods'] as $foodData) {
            $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1) * $nights;
        }
        
        // Update final_totals to include food cost
        if (!isset($booking['final_totals'])) {
            $booking['final_totals'] = [
                'accommodation_total' => 0,
                'activities_total' => 0,
                'food_total' => $foodTotal,
                'grand_total' => 0
            ];
        } else {
            $booking['final_totals']['food_total'] = $foodTotal;
            $booking['final_totals']['grand_total'] =
                ($booking['final_totals']['accommodation_total'] ?? 0) +
                ($booking['final_totals']['activities_total'] ?? 0);
        }
        
        $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [2]));
        $booking['current_step'] = 3;

        session(['booking' => $booking]);

        return response()->json([
            'success' => true,
            'message' => 'Food selection saved successfully'
        ]);
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
                    if ($baseRate > 0) {
                        return $baseRate;
                    }
                }
            }

            // If no specific config applies, use first config's rate
            if (!empty($priceConfigs[0])) {
                $config = $priceConfigs[0];
                $rates = $config['rates'] ?? [];
                $baseRate = $rates[$rateKey] ?? 0;
                if ($baseRate > 0) {
                    return $baseRate;
                }
            }
        }

        // Fallback to staycation's price field if it exists
        if (!empty($staycation->price) && $staycation->price > 0) {
            return $staycation->price;
        }

        // LAST RESORT: Only if admin hasn't configured anything
        // Log a warning so you know pricing is incomplete
        Log::warning("No price configuration found for staycation: {$staycation->name} (ID: {$staycation->id})");
        
        return 0; // Return 0 instead of guessing with hardcoded values
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

    /**
     * Calculate mattress fee for a given quantity and number of nights
     * Centralized method to avoid repeating the calculation
     */
    protected function calculateMattressFee($mattressCount, $nights)
    {
        $pricePerNight = BookingSetting::getValue('mattress_price_per_night', 0);
        return (int)$mattressCount * (int)$pricePerNight * max(1, $nights);
    }

    /**
     * Calculate pet fee for a given pet count
     * Centralized method to avoid repeating the calculation
     */
    protected function calculatePetFee($petCount)
    {
        $petPrice = BookingSetting::getValue('pet_price', 0);
        return (int)$petCount * (int)$petPrice;
    }







}
