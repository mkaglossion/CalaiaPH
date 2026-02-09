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
    protected $pricingService;
    protected $paymongo;

    public function __construct(
        BookingService $bookingService,
        PaymentService $paymentService,
        InventoryService $inventoryService,
        PricingService $pricingService
    ) {
        $this->bookingService = $bookingService;
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
        $this->pricingService = $pricingService;
        $this->paymongo = new \Paymongo\PaymongoClient(config('services.paymongo.secret_key'));
    }

    /**
     * CENTRALIZED PRICING METHOD - Use this for ALL staycation pricing calculations
     * 
     * Calculates the total price for a staycation room by iterating through each night
     * and determining the applicable rate based on peak periods, day of week, and price configs.
     * 
     * @param Staycation $staycation The staycation model
     * @param string $checkinDate (Y-m-d format)
     * @param string $checkoutDate (Y-m-d format)
     * @param int $quantity Number of units (guests for regular rooms, qty for BYOT/Car Camping)
     * @param int $extraMattresses Number of extra mattresses
     * @param int $numPets Number of pets
     * @param bool $hasPets Whether pets are included
     * @return array With keys: accommodation_price, mattress_price, pet_price, total_price, breakdown
     */
    private function calculateCentralizedStaycationPrice(
        Staycation $staycation,
        string $checkinDate,
        string $checkoutDate,
        int $quantity = 1,
        int $extraMattresses = 0,
        int $numPets = 0,
        bool $hasPets = false
    ): array {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);
        $nights = $checkin->diffInDays($checkout);

        // Determine room type
        $roomNameLower = strtolower($staycation->name ?? '');
        $isBYOT = str_contains($roomNameLower, 'byot') || str_contains($roomNameLower, 'bring your own tent') || str_contains($roomNameLower, 'bring your own');
        $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');
        $isSpecialRoom = $isBYOT || $isCarCamping;

        $accommodationPrice = 0;
        $breakdown = [];

        // Iterate through each night to get the correct rate
        $current = $checkin->copy();
        while ($current->lt($checkout)) {
            // Get the season for this specific date
            $seasonType = $this->getSeasonTypeForDate($current);
            $seasonKey = $this->mapSeasonToKey($seasonType);

            // Get the rate from price configs
            $nightlyRate = $this->getStaycationNightlyRate($staycation, $current, $seasonKey);

            // For BYOT/Car Camping, multiply by quantity
            if ($isSpecialRoom) {
                $accommodationPrice += $nightlyRate * $quantity;
            } else {
                // For regular rooms, just add the nightly rate (no quantity multiplier)
                $accommodationPrice += $nightlyRate;
            }

            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'day_of_week' => $current->format('l'),
                'season' => $seasonType,
                'rate' => $nightlyRate,
                'total_for_night' => $isSpecialRoom ? $nightlyRate * $quantity : $nightlyRate
            ];

            $current->addDay();
        }

        $mattressPrice = 0;
        $petPrice = 0;

        // Calculate extra fees for non-BYOT/Car Camping rooms
        if (!$isSpecialRoom) {
            $mattressPrice = $this->calculateMattressFee($extraMattresses, $nights);
            if ($hasPets && $numPets > 0) {
                $petPrice = $this->calculatePetFee($numPets);
            }
        }

        $totalPrice = $accommodationPrice + $mattressPrice + $petPrice;

        return [
            'accommodation_price' => $accommodationPrice,
            'mattress_price' => $mattressPrice,
            'pet_price' => $petPrice,
            'total_price' => $totalPrice,
            'breakdown' => $breakdown,
            'is_special_room' => $isSpecialRoom,
            'nights' => $nights,
            'quantity' => $quantity
        ];
    }

    /**
     * Get season type for a specific date
     */
    private function getSeasonTypeForDate(\Carbon\Carbon $date): string
    {
        $overlappingPeriods = PeakPeriod::where(function($query) use ($date) {
            $query->where('start_date', '<=', $date->toDateString())
                  ->where('end_date', '>=', $date->toDateString());
        })
        ->with('peakType')
        ->get();

        $seasonType = 'Non-Peak';
        $highestPriority = -1;

        foreach ($overlappingPeriods as $period) {
            $priority = $period->peakType->priority ?? 0;
            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $seasonType = $period->peakType->name;
            }
        }

        return $seasonType;
    }

    /**
     * Map season name to rate key
     */
    private function mapSeasonToKey(string $seasonType): string
    {
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];
        return $seasonKeyMap[$seasonType] ?? 'nonPeak';
    }

    /**
     * Calculate min and max nightly rates for a staycation within a date range
     * Returns array with keys: min_price, max_price, min_label, max_label
     */
    private function calculatePriceRangeForDateRange(Staycation $staycation, string $checkinDate, string $checkoutDate, string $seasonKey): array
    {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);
        
        $prices = [];
        $current = $checkin->copy();
        
        while ($current->lt($checkout)) {
            $rate = $this->getStaycationNightlyRate($staycation, $current, $seasonKey);
            if ($rate > 0) {
                $prices[] = [
                    'rate' => $rate,
                    'day' => $current->format('D'),
                    'date' => $current->format('Y-m-d')
                ];
            }
            $current->addDay();
        }
        
        if (empty($prices)) {
            // No prices found, return defaults
            return [
                'min_price' => 0,
                'max_price' => 0,
                'min_label' => '—',
                'max_label' => '—',
                'prices' => []
            ];
        }
        
        // Get min and max prices
        $minPrice = min(array_column($prices, 'rate'));
        $maxPrice = max(array_column($prices, 'rate'));
        
        return [
            'min_price' => (int) $minPrice,
            'max_price' => (int) $maxPrice,
            'min_label' => '₱' . number_format((int) $minPrice, 0),
            'max_label' => '₱' . number_format((int) $maxPrice, 0),
            'prices' => $prices
        ];
    }

    /**
     * Get the nightly rate for a staycation on a specific date
     */
    private function getStaycationNightlyRate(Staycation $staycation, \Carbon\Carbon $date, string $seasonKey): float
    {
        if (empty($staycation->price_configs)) {
            return (float)($staycation->price ?? 0);
        }

        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = $date->format('D');

        // 1. Check for specific date match
        foreach ($staycation->price_configs as $config) {
            if (isset($config['dates'])) {
                $dates = is_array($config['dates']) ? $config['dates'] : [$config['dates']];
                if (in_array($dateStr, $dates)) {
                    return (float)($config['rates'][$seasonKey] ?? 0);
                }
            }
        }

        // 2. Check for day-of-week match
        foreach ($staycation->price_configs as $config) {
            if (isset($config['days']) && in_array($dayOfWeek, $config['days'])) {
                return (float)($config['rates'][$seasonKey] ?? 0);
            }
        }

        // 3. Fallback to first config
        if (!empty($staycation->price_configs) && isset($staycation->price_configs[0]['rates'])) {
            return (float)($staycation->price_configs[0]['rates'][$seasonKey] ?? 0);
        }

        // 4. Final fallback to staycation's base price
        return (float)($staycation->price ?? 0);
    }

    /**
     * Get base rate for accommodation display
     */
    private function getAccommodationBaseRate($accommodation)
    {
        $name = strtolower($accommodation['staycation']['name']);
        $isBYOT = str_contains($name, 'byot');
        $isCarCamping = str_contains($name, 'car camping');

        if (str_contains($name, 'standard')) {
            return 4800; // weekday rate
        } elseif (str_contains($name, 'premium')) {
            return 5760; // weekday rate
        } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
            return 3000;
        } elseif ($isBYOT || $isCarCamping) {
            return 300;
        }

        return $accommodation['staycation']['price'] ?? 0;
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
     * Calculate total price for staycation accommodations using CENTRALIZED method
     */
    private function calculateStaycationTotal(array &$accommodations, string $checkinDate, string $checkoutDate): float
    {
        $total = 0;

        foreach ($accommodations as &$acc) {
            if (empty($acc['room_id']) && empty($acc['accommodation_id'])) continue;

            $roomId = $acc['room_id'] ?? $acc['accommodation_id'];
            $staycation = Staycation::find($roomId);
            if (!$staycation) continue;

            // Use CENTRALIZED pricing method
            $quantity = (int)($acc['extra_mattress'] ?? ($acc['quantity'] ?? 1));
            $hasPets = ($acc['pets'] ?? 'no') === 'yes' && ($acc['pet_agreement'] ?? false);
            $numPets = (int)($acc['num_pets'] ?? 0);
            
            $pricingResult = $this->calculateCentralizedStaycationPrice(
                $staycation,
                $checkinDate,
                $checkoutDate,
                $quantity,
                (int)($acc['extra_mattress'] ?? 0),
                $numPets,
                $hasPets
            );
            
            // Update accommodation with calculated prices
            $acc['accommodation_price'] = $pricingResult['accommodation_price'];
            $acc['mattress_price'] = $pricingResult['mattress_price'];
            $acc['pet_price'] = $pricingResult['pet_price'];
            $acc['total_price'] = $pricingResult['total_price'];
            
            $total += $pricingResult['total_price'];
        }

        return $total;
    }

    public function staycationPayment()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete your staycation booking first.');
        }

        $checkinDate = $booking['checkin'];
        $checkoutDate = $booking['checkout'];
        
        // Recalculate accommodation prices using CENTRALIZED method for consistency
        foreach ($booking['accommodations'] as $roomId => &$room) {
            $actualRoomId = $room['accommodation_id'] ?? $roomId;
            $staycation = \App\Models\Staycation::find($actualRoomId);
            
            if ($staycation) {
                // Use centralized pricing method
                $pricingResult = $this->calculateCentralizedStaycationPrice(
                    $staycation,
                    $checkinDate,
                    $checkoutDate,
                    (int)($room['extra_mattress'] ?? 1), // quantity
                    (int)($room['extra_mattress'] ?? 0),  // extra mattresses (for regular rooms)
                    (int)($room['num_pets'] ?? 0),        // number of pets
                    ($room['pet_agreement'] ?? false)      // has pets?
                );
                
                // Update room with calculated prices
                $room['accommodation_price'] = $pricingResult['accommodation_price'];
                $room['mattress_price'] = $pricingResult['mattress_price'];
                $room['pet_price'] = $pricingResult['pet_price'];
                $room['total_price'] = $pricingResult['total_price'];
            }
        }
        
        // Calculate accommodation total
        $accommodationTotal = collect($booking['accommodations'] ?? [])->sum(function($acc) {
            return $acc['total_price'] ?? 0;
        });
        
        // Calculate activities total
        $activitiesTotal = collect($booking['selected_activities'] ?? [])->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });
        
        // Calculate food total
        $foodTotal = 0;
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $foodData) {
                $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1);
            }
        }
        
        // Store final totals in booking session
        $booking['final_totals'] = [
            'accommodation_total' => $accommodationTotal,
            'activities_total' => $activitiesTotal,
            'food_total' => $foodTotal,
            'grand_total' => $accommodationTotal + $activitiesTotal + $foodTotal
        ];
        
        session(['booking' => $booking]);

        // Get selected foods from current booking session only
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

    public function initiatePayment(Request $request)
    {
        // Debug: Log all request data
        Log::info('Initiate Payment Request Data:', $request->all());

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:paymongo,qr,gcash,paymaya,bank,qrph,grab_pay,billease',
            'special_requests' => 'nullable|string|max:1000',
            'cancellation_policy' => 'required|string|in:standard,flexible,nonrefundable',
            'terms_accepted' => 'accepted',
        ]);

        // Get booking data from session
        $booking = session('booking', []);
        Log::info('Session booking data:', $booking);

        if (empty($booking)) {
            return redirect()->back()->with('error', 'Booking session expired. Please start over.');
        }

        // Calculate grand total
        $grandTotal = 0;
        if (!empty($booking['final_totals']['grand_total'])) {
            $grandTotal = $booking['final_totals']['grand_total'];
        } else {
            // Fallback calculation
            if (!empty($booking['accommodations'])) {
                foreach ($booking['accommodations'] as $acc) {
                    $grandTotal += $acc['total_price'] ?? 0;
                }
            }
            if (!empty($booking['selected_foods'])) {
                foreach ($booking['selected_foods'] as $food) {
                    $nights = max(1, \Carbon\Carbon::parse($booking['checkin'])->diffInDays(\Carbon\Carbon::parse($booking['checkout'])));
                    $grandTotal += ($food['price'] ?? 0) * ($food['quantity'] ?? 1) * $nights;
                }
            }
            if (!empty($booking['selected_activities'])) {
                foreach ($booking['selected_activities'] as $activity) {
                    $grandTotal += ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                }
            }
        }

        Log::info('Calculated grand total:', ['total' => $grandTotal]);

        // Build line items for PayMongo from the exact same breakdown shown on the payment page
        $lineItems = [];
        
        // Get the final totals that were calculated and stored in the booking
        $accommodationTotal = $booking['final_totals']['accommodation_total'] ?? 0;
        $activitiesTotal = $booking['final_totals']['activities_total'] ?? 0;
        $foodTotal = $booking['final_totals']['food_total'] ?? 0;
        
        // 1. Add accommodation line items first
        if (!empty($booking['accommodations'])) {
            foreach ($booking['accommodations'] as $acc) {
                if (!empty($acc) && !empty($acc['total_price'])) {
                    $lineItems[] = [
                        'amount' => (int)($acc['total_price'] * 100), // Total price in centavos
                        'currency' => 'PHP',
                        'name' => $acc['room_name'] ?? 'Accommodation',
                        'quantity' => 1,
                    ];
                }
            }
        }
        
        // 2. Add food line items second
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $food) {
                if (!empty($food) && !empty($food['name']) && !empty($food['price'])) {
                    // Calculate total amount for this food item: unit_price × quantity (per order, not per night)
                    $foodQty = $food['quantity'] ?? 1;
                    $totalAmount = (int)($food['price'] * $foodQty * 100); // Total in centavos
                    
                    $lineItems[] = [
                        'amount' => $totalAmount, // Total amount in centavos
                        'currency' => 'PHP',
                        'name' => $food['name'],
                        'quantity' => $foodQty, // Quantity as ordered
                    ];
                }
            }
        }
        
        // 3. Add activities line items last
        if (!empty($booking['selected_activities'])) {
            foreach ($booking['selected_activities'] as $activity) {
                if (!empty($activity) && !empty($activity['name']) && !empty($activity['price'])) {
                    // Calculate total amount for activities: unit_price × number of guests
                    $guestCount = $activity['guests'] ?? 1;
                    $totalAmount = (int)($activity['price'] * $guestCount * 100); // Total in centavos
                    
                    $lineItems[] = [
                        'amount' => $totalAmount, // Total amount in centavos
                        'currency' => 'PHP',
                        'name' => $activity['name'],
                        'quantity' => $guestCount,
                    ];
                }
            }
        }

        Log::info('Line items being sent to PayMongo (in order):', $lineItems);

        // Store booking and payment data in session
        session([
            'staycation_booking_data' => $booking,
            'staycation_payment_data' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'special_requests' => $request->special_requests,
                'cancellation_policy' => $request->cancellation_policy,
                'payment_method' => $request->payment_method,
            ]
        ]);

        $successUrl = url('/booking/payment/success?checkout_id={CHECKOUT_ID}');
        $failedUrl = url('/booking/staycation/payment');

        Log::info('Creating PayMongo checkout', [
            'amount' => (int)($grandTotal * 100),
            'success_url' => $successUrl,
            'failed_url' => $failedUrl,
            'line_items_count' => count($lineItems),
            'accommodation_total' => $accommodationTotal,
            'activities_total' => $activitiesTotal,
            'food_total' => $foodTotal
        ]);

        try {
            $checkout = $this->paymentService->createCheckout((int)($grandTotal * 100), 'staycation', $successUrl, $failedUrl, $lineItems);
            Log::info('PayMongo checkout created successfully', ['checkout_url' => $checkout->checkout_url]);
            return redirect($checkout->checkout_url);
        } catch (\Exception $e) {
            Log::error('PayMongo checkout creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|string'
        ]);

        $amount = (int) ($request->amount * 100); // Convert to centavos

        try {
            $paymentIntent = $this->paymentService->createPaymentIntent($amount, $request->type);
            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $checkoutId = $request->query('checkout_id');

        if (!$checkoutId) {
            return redirect()->route('booking.payment')->with('error', 'Invalid payment session.');
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
                $bookingData = session('staycation_booking_data');
                $paymentData = session('staycation_payment_data');

                if (!$bookingData || !$paymentData) {
                    return redirect()->route('booking.payment')->with('error', 'Booking data not found.');
                }

                // Process the payment
                $paymentData['checkout_id'] = $checkoutId;
                $result = $this->paymentService->processStaycationPayment($bookingData, $paymentData);

                // Clear session
                session()->forget(['staycation_booking_data', 'staycation_payment_data']);

                return $result; // This should be the redirect to confirmation
            } else {
                return redirect()->route('booking.payment')->with('error', 'Payment was not successful.');
            }
        } catch (\Exception $e) {
            Log::error('PayMongo checkout verification failed: ' . $e->getMessage());
            return redirect()->route('booking.payment')->with('error', 'Payment verification failed.');
        }
    }

    public function paymentFailed(Request $request)
    {
        $checkoutId = $request->query('checkout_id');

        if (!$checkoutId) {
            return redirect()->route('booking.payment')->with('error', 'Invalid payment session.');
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

            // Log the failure reason
            Log::warning('PayMongo payment failed', [
                'checkout_id' => $checkoutId,
                'status' => $checkout->payment_intent ? $checkout->payment_intent->status : 'unknown',
                'error' => $checkout->payment_intent ? $checkout->payment_intent->last_payment_error : null
            ]);

            return redirect()->route('booking.payment')->with('error', 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('PayMongo checkout verification failed on failure: ' . $e->getMessage());
            return redirect()->route('booking.payment')->with('error', 'Payment verification failed.');
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

                    // Use CENTRALIZED pricing method for consistency across all booking steps
                    $quantity = (int)($room['extra_mattress'] ?? ($room['quantity'] ?? 1));
                    $extraMattresses = !($isBYOT || $isCarCamping) ? (int)($room['extra_mattress'] ?? 0) : 0;
                    $hasPets = ($room['pets'] ?? 'no') === 'yes' && ($room['pet_agreement'] ?? false);
                    $numPets = (int)($room['num_pets'] ?? 0);
                    
                    $pricingResult = $this->calculateCentralizedStaycationPrice(
                        $staycation,
                        $checkinDate,
                        $checkoutDate,
                        $quantity,
                        $extraMattresses,
                        $numPets,
                        $hasPets
                    );
                    
                    // Extract pricing components
                    $accommodationPrice = $pricingResult['accommodation_price'];
                    $mattressPrice = $pricingResult['mattress_price'];
                    $petPrice = $pricingResult['pet_price'];
                    $totalPrice = $pricingResult['total_price'];

                    // Calculate costs for display
                    $adults = (int)($room['adults'] ?? 1);

                    $normalized[$roomId] = [
                        'room_id'        => $roomId,
                        'accommodation_id' => $roomId,
                        'accommodation_price' => $accommodationPrice,
                        'adults'         => $adults,
                        'children'       => (int)($room['children'] ?? 0),
                        'extra_mattress' => (int)($room['extra_mattress'] ?? 0),
                        'quantity'       => $quantity,
                        'pets'           => ($room['pets'] ?? 'no'),
                        'pet_agreement'  => (bool)($room['pet_agreement'] ?? false),
                        'num_pets'       => $numPets,
                        'base_price'     => $accommodationPrice,
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

        // Determine season for the booking date range
        $checkinCarbon = \Carbon\Carbon::parse($checkin);
        $checkoutCarbon = \Carbon\Carbon::parse($checkout);
        
        // Find the highest priority peak period that overlaps with the booking dates
        $overlappingPeriods = \App\Models\PeakPeriod::where(function($query) use ($checkin, $checkout) {
            $query->whereBetween('start_date', [$checkin, $checkout])
                  ->orWhereBetween('end_date', [$checkin, $checkout])
                  ->orWhere(function($q) use ($checkin, $checkout) {
                      $q->where('start_date', '<=', $checkin)
                        ->where('end_date', '>=', $checkout);
                  });
        })
        ->with('peakType')
        ->get();
        
        $currentSeason = 'Non-Peak';
        $highestPriority = -1;
        
        foreach ($overlappingPeriods as $period) {
            $priority = $period->peakType->priority ?? 0;
            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $currentSeason = $period->peakType->name;
            }
        }
        
        Log::info('Season for booking dates in staycationAvailability: ' . $currentSeason);

        // Map season to rate key
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];
        $seasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';

        // Map season to rate key
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];
        $seasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';
        // Get weekend day IDs
        $weekendDayIds = DayTypeDay::whereHas('dayType', fn($q) => $q->where('name', 'Weekend'))
            ->pluck('weekday')
            ->toArray();

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

            // Calculate the rate per night using the pricing service
            $currentRate = $this->pricingService->getStaycationRate($staycation, $checkinCarbon, $seasonKey);

            // If no rate configured for the season, use staycation's price field
            if ($currentRate <= 0) {
                $currentRate = $staycation->price ?? 0;
            }

            // If no rate configured, use type-based default
            if ($currentRate <= 0) {
                $currentRate = $type === 'glamping' ? 5500 : 3000;
            }

            // Calculate price range for the selected date range
            $priceRange = $this->calculatePriceRangeForDateRange($staycation, $checkin, $checkout, $seasonKey);

            $roomDetails[$staycation->id] = [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'type' => $type,
                'description' => $description,
                'image' => $image,
                'status' => $status,
                'available_quantity' => $minOverRange,
                'total_quantity' => $baseQty,
                'price' => $currentRate, // Per night rate for current season
                'price_range' => $priceRange, // NEW: Price range for displayed dates
                'price_configs' => $staycation->price_configs ?? []
            ];
        }

        return view('booking.staycation-availability', compact('staycations', 'availability', 'roomDetails', 'checkin', 'checkout', 'currentSeason'));
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

        // Pull food items from database with images and texts
        // Filter for staycation: only messhall, cook_your_own, and special options
        $foodMenus = Food::with(['swiperImages', 'texts'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('category', ['messhall', 'cook_your_own'])
                      ->orWhere('is_special_option', true);
            })
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

        // Calculate food total from selected foods
        $foodTotal = 0;
        foreach ($validated['selected_foods'] as $foodData) {
            $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1);
        }
        
        // Update final_totals to include food cost
        if (!isset($booking['final_totals'])) {
            $booking['final_totals'] = [
                'accommodation_total' => 0,
                'activities_total' => 0,
                'food_total' => $foodTotal,
                'grand_total' => $foodTotal
            ];
        } else {
            $booking['final_totals']['food_total'] = $foodTotal;
            $booking['final_totals']['grand_total'] = 
                ($booking['final_totals']['accommodation_total'] ?? 0) + 
                ($booking['final_totals']['activities_total'] ?? 0) + 
                $foodTotal;
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

    public function getStaycationPricesApi(Request $request)
    {
        $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin'
        ]);

        $checkin = $request->checkin;
        $checkout = $request->checkout;

        // Get all staycations with pricing
        $staycations = Staycation::all();

        $prices = [];
        foreach ($staycations as $staycation) {
            $checkinCarbon = \Carbon\Carbon::parse($checkin);
            $season = $this->determineSeasonTypeForDate($checkinCarbon);
            $seasonKey = str_replace('-', '', ucwords($season, '-')); // non-peak -> nonPeak

            $rate = $this->pricingService->getStaycationRate($staycation, $checkinCarbon, $seasonKey);

            $prices[$staycation->id] = [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'price' => $rate,
                'season' => $season
            ];
        }

        return response()->json([
            'success' => true,
            'prices' => $prices,
            'checkin' => $checkin,
            'checkout' => $checkout
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
