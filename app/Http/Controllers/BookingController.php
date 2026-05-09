<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Mail\BookingConfirmation;
use DateTime;
// use App\Services\BookingService; // Removed duplicate
// use App\Services\PaymentService; // Removed duplicate
// use App\Services\InventoryService; // Removed duplicate
use App\Services\ValidationService;

use App\Models\StaycationAccommodation;
use App\Models\Staycation;
use App\Models\Food;
use App\Models\FoodPackage;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\DayTour;
use App\Models\Holiday;
use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakPeriod;
use App\Models\PeakType;
use App\Models\PricingConfig;
use App\Models\StaycationInventory;
use Carbon\Carbon;
use App\Services\PricingService;
use App\Models\BookingSetting;
use App\Models\FoodPackageSetting;



use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\InventoryService;
// use App\Services\PricingService; // Removed duplicate
// use Illuminate\Http\Request; // Removed duplicate

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected PaymentService $paymentService;
    protected InventoryService $inventoryService;
    protected PricingService $pricingService;
    protected \Paymongo\PaymongoClient $paymongo;

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
    /**
     * @param Staycation $staycation
     * @param string $checkinDate
     * @param string $checkoutDate
     * @param int $adults
     * @param int $children
     * @param int $extraMattresses
     * @param int $numPets
     * @param bool $hasPets
     * @return array
     */
    private function calculateCentralizedStaycationPrice(
        Staycation $staycation,
        string $checkinDate,
        string $checkoutDate,
        int $adults = 1,
        int $children = 0,
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

        if ($isSpecialRoom) {
            // Always 1 night, just (adults + children) × base price (no night multiplier)
            $seasonType = $this->getSeasonTypeForDate($checkin);
            $seasonKey = $this->mapSeasonToKey($seasonType);
            $baseRate = $this->getStaycationNightlyRate($staycation, $checkin, $seasonKey);
            $totalGuests = $adults + $children;
            $accommodationPrice = $baseRate * $totalGuests;
            $breakdown[] = [
                'date' => $checkin->format('Y-m-d'),
                'day_of_week' => $checkin->format('l'),
                'season' => $seasonType,
                'rate' => $baseRate,
                'total_for_night' => $baseRate * $totalGuests
            ];
            $nights = 1; // force 1 night for display
        } else {
            // Iterate through each night to get the correct rate
            $current = $checkin->copy();
            while ($current->lt($checkout)) {
                // Get the season for this specific date
                $seasonType = $this->getSeasonTypeForDate($current);
                $seasonKey = $this->mapSeasonToKey($seasonType);

                // Get the rate from price configs
                $nightlyRate = $this->getStaycationNightlyRate($staycation, $current, $seasonKey);
                $accommodationPrice += $nightlyRate;

                // Determine day type (Weekday/Weekend)
                $dayType = 'Weekday';
                $dayOfWeek = $current->format('D');
                if ($dayOfWeek === 'Sat' || $dayOfWeek === 'Sun') {
                    $dayType = 'Weekend';
                }

                $breakdown[] = [
                    'date' => $current->format('Y-m-d'),
                    'day_of_week' => $current->format('l'),
                    'season' => $seasonType,
                    'day_type' => $dayType,
                    'rate' => $nightlyRate,
                    'total_for_night' => $nightlyRate
                ];

                $current->addDay();
            }
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
            'adults' => $adults,
            'children' => $children,
            'total_guests' => $adults + $children
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
     *
     * FIXED: Now determines season type for EACH individual day instead of using
     * a single season key for the entire range.
     */
    private function calculatePriceRangeForDateRange(Staycation $staycation, string $checkinDate, string $checkoutDate, string $seasonKey = null): array
    {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);
        
        $prices = [];
        $current = $checkin->copy();
        
        while ($current->lt($checkout)) {
            // Determine the season type for THIS specific day (not the whole range)
            $daySeasonType = $this->getSeasonTypeForDate($current);
            $daySeasonKey = $this->mapSeasonToKey($daySeasonType);
            
            $rate = $this->pricingService->getStaycationRate($staycation, $current, $daySeasonKey);
            if ($rate > 0) {
                $prices[] = [
                    'rate' => $rate,
                    'day' => $current->format('D'),
                    'date' => $current->format('Y-m-d'),
                    'season' => $daySeasonType
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
     * Now considers both season type AND day type (weekday/weekend)
     */
    private function getStaycationNightlyRate(Staycation $staycation, \Carbon\Carbon $date, string $seasonKey): float
    {
        // Use the PricingService for consistency
        return $this->pricingService->getStaycationRate($staycation, $date, $seasonKey);
    }

    /**
     * Get base rate for accommodation display
     */
    private function getAccommodationBaseRate(array $accommodation)
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

    /**
     * Calculate day tour price in real-time for calendar
     * Used when user selects a date and updates guests
     */
    public function calculateDayTourPrice(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
        ]);

        try {
            // For day tours, we need a default day tour or just use basic pricing
            // Since we don't know which specific tour yet, we calculate a generic price
            // You might want to pass a tour_id if available
            $date = Carbon::parse($request->date);
            
            // Get a sample day tour for pricing
            $dayTour = DayTour::where('is_active', true)->first();
            if (!$dayTour) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active day tours available',
                    'total' => 0
                ]);
            }

            // Use basic pricing: adults + children (at reduced rate)
            // Adjust these multipliers based on your business logic
            $adultPrice = $dayTour->price ?? 0;
            $childrenPrice = ($dayTour->price ?? 0) * 0.7; // Children at 70% price
            
            $totalPrice = ($request->adults * $adultPrice) + ($request->children * $childrenPrice);

            return response()->json([
                'success' => true,
                'total' => round($totalPrice, 2),
                'breakdown' => [
                    'adults' => $request->adults,
                    'children' => $request->children,
                    'adult_price' => round($adultPrice, 2),
                    'children_price' => round($childrenPrice, 2),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating price: ' . $e->getMessage(),
                'total' => 0
            ], 500);
        }
    }

    /**
     * Calculate staycation price in real-time for calendar
     * Used when user selects dates and updates rooms/guests
     */
    public function calculateStaycationPriceCalendar(Request $request)
    {
        $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'rooms' => 'required|integer|min:1|max:4',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'pets' => 'required|integer|min:0',
        ]);

        try {
            $checkin = $request->checkin;
            $checkout = $request->checkout;
            
            // Get a sample staycation for pricing
            $staycation = Staycation::whereNotNull('price')->first();
            if (!$staycation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staycations available',
                    'total' => 0
                ]);
            }

            // Calculate price using the pricing service
            $priceResult = $this->calculateCentralizedStaycationPrice(
                $staycation,
                $checkin,
                $checkout,
                $request->adults,
                $request->children,
                0, // extra_mattresses - not needed for calendar preview
                $request->pets,
                $request->pets > 0
            );

            return response()->json([
                'success' => true,
                'total' => round($priceResult['total_price'], 2),
                'breakdown' => $priceResult['breakdown'] ?? [],
                'nights' => $priceResult['nights'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating price: ' . $e->getMessage(),
                'total' => 0
            ], 500);
        }
    }

    /**
     * Calculate staycation price using centralized method via API
     */
    public function calculateStaycationPrice(Request $request)
    {
        $request->validate([
            'staycation_id' => 'required|integer',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'extra_mattresses' => 'required|integer|min:0',
            'num_pets' => 'required|integer|min:0',
            'has_pets' => 'required|boolean',
        ]);

        $staycation = Staycation::find($request->staycation_id);
        if (!$staycation) {
            return response()->json(['success' => false, 'message' => 'Staycation not found'], 404);
        }

        $pricingResult = $this->calculateCentralizedStaycationPrice(
            $staycation,
            $request->checkin_date,
            $request->checkout_date,
            $request->adults,
            $request->children,
            $request->extra_mattresses,
            $request->num_pets,
            $request->has_pets
        );

        return response()->json([
            'success' => true,
            'data' => $pricingResult,
        ]);
    }

    private function getBookingActivities(string $type)
    {
        $config = self::BOOKING_CONFIG[$type];
        $data = session($config['session_key']);
        if (!$data) {
            return redirect()->route($config['activities_error_route'])->with('error', 'No booking data found. Please start booking again.');
        }
        $activities = $this->bookingService->getActivitiesData();
        return view($config['activities_view'], [$config['var_name'] => $data, 'activities' => $activities]);
    }

    private function storeBookingActivities(Request $request, string $type, array $validationRules, string $saveKey)
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

    private function getBookingPayment(string $type)
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
        $type = $request->input('type', 'day-visit');

        // Handle different type variations
        $allowedTypes = ['day-visit', 'room', 'daytour'];
        if ($type === 'daytour') {
            $type = 'day-visit'; // Map daytour to day-visit
        }
        if (!in_array($type, $allowedTypes)) {
            $type = 'day-visit'; // Fallback to default
        }

        // Get package details
        $package = $request->input('package', 'Day Visit Package');
        $price = $request->input('price', '1000');
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

        // Fetch activities
        $activities = Activity::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        // Fetch activity packages with their activities
        $activityPackages = ActivityPackage::where('is_active', true)
            ->with(['activities' => function($q) {
                $q->select('activities.id', 'activities.name', 'activities.price')
                  ->orderBy('activities.name');
            }])
            ->latest()
            ->get();
        
        // Get selected foods from session
        $selectedFoods = $booking['selected_foods'] ?? session('selected_foods', []);
        
        // Fetch food details if we have selected foods
        $foods = [];
        if (!empty($selectedFoods)) {
            $foodIds = array_column($selectedFoods, 'id');
            $foods = \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id');
        }

            // Get selected activities from session
        $selectedActivities = $booking['selected_activities'] ?? session('selected_activities', []);
        $selectedActivityPackages = $booking['selected_activity_packages'] ?? session('selected_activity_packages', []);

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-activities', compact('booking', 'activities', 'activityPackages', 'staycations', 'selectedFoods', 'foods', 'selectedActivities', 'selectedActivityPackages', 'vatRate', 'vatRatePercentage'));
    }

    public function storeStaycationActivities(Request $request)
    {
        $booking = session('booking', []);

        if (empty($booking)) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        // Accept both individual activities and activity packages
        // Data structure from frontend: {selected_activities: [{id, name, price, ...}, {package_id, is_package, ...}]}
        try {
            $validated = $request->validate([
                'selected_activities' => 'nullable|array',
                'selected_activities.*.id' => 'required_unless:selected_activities.*.is_package,true|string|max:50',
                'selected_activities.*.package_id' => 'required_if:selected_activities.*.is_package,true|integer',
                'selected_activities.*.name' => 'required|string|max:255',
                'selected_activities.*.category' => 'required|string|max:50',
                'selected_activities.*.price' => 'required|numeric|min:0',
                'selected_activities.*.quantity' => 'sometimes|integer|min:1',
                'selected_activities.*.is_package' => 'boolean',
                'selected_activities.*.included_activities' => 'nullable|array',
            ]);

            $selectedActivities = $validated['selected_activities'] ?? [];

            // Process and separate individual activities and packages
            $activities = [];
            $activityPackages = [];

            foreach ($selectedActivities as $item) {
                if ($item['is_package'] ?? false) {
                    $activityPackages[] = $item;
                } else {
                    $activities[] = $item;
                }
            }

            // Store in booking session
            $booking['selected_activities'] = $activities;
            $booking['selected_activity_packages'] = $activityPackages;

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

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

        // Calculate activities total (individual items)
        $activitiesTotal = collect($booking['selected_activities'] ?? [])->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['quantity'] ?? 1);
        });

        // Calculate activity packages total
        $packagesTotal = collect($booking['selected_activity_packages'] ?? [])->sum(function($package) {
            return ($package['price'] ?? 0) * ($package['quantity'] ?? 1);
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
            'activities_total' => $activitiesTotal + $packagesTotal,
            'food_total' => $foodTotal,
            'grand_total' => $accommodationTotal + $activitiesTotal + $packagesTotal + $foodTotal
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
            $adults = (int)($acc['adults'] ?? 1);
            $children = (int)($acc['children'] ?? 0);
            $numPets = (int)($acc['num_pets'] ?? 0);
            $hasPets = (($acc['pets'] ?? 'no') === 'yes') && ($numPets > 0);
            
            $pricingResult = $this->calculateCentralizedStaycationPrice(
                $staycation,
                $checkinDate,
                $checkoutDate,
                $adults,
                $children,
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
                $numPets = (int)($room['num_pets'] ?? 0);
                $hasPets = (($room['pets'] ?? 'no') === 'yes') && ($numPets > 0);

                $pricingResult = $this->calculateCentralizedStaycationPrice(
                    $staycation,
                    $checkinDate,
                    $checkoutDate,
                    (int)($room['adults'] ?? 1),
                    (int)($room['children'] ?? 0),
                    (int)($room['extra_mattress'] ?? 0),
                    $numPets,
                    $hasPets
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
        
        // Calculate activities total (including both individual activities and packages)
        $activitiesTotal = collect($booking['selected_activities'] ?? [])->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });
        
        // Add activity packages to activities total
        $activitiesTotal += collect($booking['selected_activity_packages'] ?? [])->sum(function($package) {
            return ($package['price'] ?? 0) * ($package['quantity'] ?? 1);
        });
        
        // Calculate nights
        $nights = 1; // default
        if (!empty($checkinDate) && !empty($checkoutDate)) {
            $nights = max(1, \Carbon\Carbon::parse($checkinDate)->diffInDays(\Carbon\Carbon::parse($checkoutDate)));
        }
        
        // Calculate food total
        $foodTotal = 0;
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $foodData) {
                $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1); // No nights multiplication
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

        // Get selected activities and activity packages from session
        $selectedActivities = $booking['selected_activities'] ?? [];
        $selectedActivityPackages = $booking['selected_activity_packages'] ?? [];

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-payment', compact('booking', 'selectedFoods', 'foods', 'staycations', 'selectedActivities', 'selectedActivityPackages', 'vatRate', 'vatRatePercentage'));
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
        
        if ($request->has('selected_activity_packages')) {
            $postedActivityPackages = json_decode($request->input('selected_activity_packages', '[]'), true);
            if (!empty($postedActivityPackages)) {
                $staycationBooking['selected_activity_packages'] = $postedActivityPackages;
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
        // Initialize booking data variables
        $staycationBooking = session('booking', []);
        $bookingData = session('booking_data');
        $dayvisitBooking = session('dayvisit_booking_data');

        // Determine which booking data to use
        if (!empty($staycationBooking) && isset($staycationBooking['booking_type']) && $staycationBooking['booking_type'] === 'staycation') {
            $booking = $staycationBooking;
        } elseif (!empty($dayvisitBooking)) {
            $booking = $dayvisitBooking;
        } else {
            $booking = $bookingData;
        }

        // Debug: Log all request data
        Log::info('Initiate Payment Request Data:', $request->all());

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'nullable|string|in:paymongo,qr,gcash,paymaya,bank,qrph,grab_pay,billease',
            'applied_promo_id' => 'nullable|exists:promo_codes,id',
            'applied_promo_code' => 'nullable|string',
            'applied_discount_amount' => 'nullable|numeric|min:0',
        ]);

        $lineItems = [];
        $grandTotal = 0.0; // in PHP (not centavos)

        // 1. Add accommodation line items first
        if (!empty($booking['accommodations'])) {
            foreach ($booking['accommodations'] as $acc) {
                if (!empty($acc) && !empty($acc['total_price'])) {
                    $unit = (float)($acc['total_price']);
                    $qty = 1;
                    $grandTotal += $unit * $qty;
                    $lineItems[] = [
                        'name' => $acc['room_name'] ?? 'Accommodation',
                        'unit_price' => $unit,
                        'quantity' => $qty,
                    ];
                }
            }
        }

        // 2. Add food line items second
        if (!empty($booking['selected_foods'])) {
            foreach ($booking['selected_foods'] as $food) {
                if (!empty($food) && !empty($food['name']) && isset($food['price'])) {
                    $foodQty = $food['quantity'] ?? 1;
                    $unit = (float)$food['price'];
                    $grandTotal += $unit * $foodQty;
                    $lineItems[] = [
                        'name' => $food['name'],
                        'unit_price' => $unit,
                        'quantity' => $foodQty,
                    ];
                }
            }
        }

        // 3. Add activities line items
        if (!empty($booking['selected_activities'])) {
            foreach ($booking['selected_activities'] as $activity) {
                if (!empty($activity) && !empty($activity['name']) && isset($activity['price'])) {
                    $guestCount = $activity['guests'] ?? 1;
                    $unit = (float)$activity['price'];
                    $grandTotal += $unit * $guestCount;
                    $lineItems[] = [
                        'name' => $activity['name'],
                        'unit_price' => $unit,
                        'quantity' => $guestCount,
                    ];
                }
            }
        }

        // 4. Add activity packages line items
        if (!empty($booking['selected_activity_packages'])) {
            foreach ($booking['selected_activity_packages'] as $package) {
                if (!empty($package) && !empty($package['name']) && isset($package['price'])) {
                    $packageQty = $package['quantity'] ?? 1;
                    $unit = (float)$package['price'];
                    $grandTotal += $unit * $packageQty;
                    $lineItems[] = [
                        'name' => $package['name'],
                        'unit_price' => $unit,
                        'quantity' => $packageQty,
                    ];
                }
            }
        }

        Log::info('Line items being sent to PayMongo (in order):', $lineItems);
        Log::info('Calculated grand total (from line items):', ['total' => $grandTotal]);

        // Apply promo code discount if present
        $promoDiscount = 0;
        $appliedPromo = null;
        if ($request->filled('applied_promo_id') && $request->filled('applied_discount_amount')) {
            $promoDiscount = (float) $request->applied_discount_amount;
            $grandTotal = max(0, $grandTotal - $promoDiscount);

            // Get promo details and increment usage
            $appliedPromo = \App\Models\PromoCode::find($request->applied_promo_id);
            if ($appliedPromo) {
                $appliedPromo->increment('usage_count');
            }

            Log::info('Promo code applied:', [
                'code' => $request->applied_promo_code,
                'discount' => $promoDiscount,
                'new_total' => $grandTotal
            ]);
        }

        // Calculate dynamic VAT using VatService
        $vatService = app(\App\Services\VatService::class);
        $vatBreakdown = $vatService->generateBreakdown((float) $grandTotal, false); // false = VAT exclusive (we add VAT)
        $grandTotalWithVat = $vatBreakdown['gross_amount']; // This includes VAT
        $vatAmount = $vatBreakdown['vat_amount'];

        Log::info('Grand total with VAT:', ['subtotal' => $grandTotal, 'vat' => $vatAmount, 'total' => $grandTotalWithVat]);

        // Use single line item for PayMongo to avoid issues with negative discounts
        $lineItems = null;

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
                'promo_code' => $request->applied_promo_code,
                'promo_discount' => $promoDiscount,
                'promo_id' => $request->applied_promo_id,
            ]
        ]);

        $successUrl = url('/booking/payment/success?checkout_id={CHECKOUT_ID}');
        $failedUrl = url('/booking/staycation/payment');

        // Generate a reference id for this checkout so it appears on PayMongo checkout
        $referenceId = 'TXN-' . strtoupper(uniqid());
        // Persist reference in session payment data
        session()->put('staycation_payment_data.reference_id', $referenceId);

        Log::info('Creating PayMongo checkout', [
            'amount' => $grandTotalWithVat * 100,
            'success_url' => $successUrl,
            'failed_url' => $failedUrl,
            'line_items_count' => is_array($lineItems) ? count($lineItems) : 0,
            'reference_id' => $referenceId,
            'customer_email' => $request->email,
        ]);

        try {
            $checkout = $this->paymentService->createCheckout((int)round($grandTotalWithVat * 100), 'staycation', $successUrl, $failedUrl, $lineItems, $referenceId, $request->email);
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
            $totalAvailable = 0;
            if (Schema::hasTable('staycation_inventories')) {
                $totalAvailable = \App\Models\StaycationInventory::where('date', $key)
                    ->sum('available_quantity');
            }

            $inventory[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];
        }

        // Get total quantity for fallback
        $totalQuantity = 0;

        // Build day tour availability data
        $dayTourInventory = [];
        $dayTours = \App\Models\DayTour::where('is_active', true)->get();
        $totalDayTourGroups = $dayTours->sum('max_groups_per_day');

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(\Carbon\Carbon::today())) {
                $dayTourInventory[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available groups for this date from day tour inventory
            $totalAvailableGroups = \App\Models\DayTourInventory::where('date', $key)
                ->where('is_blocked', false)
                ->sum('available_groups');

            // If no inventory records exist for this date, fall back to sum of max_groups_per_day
            if ($totalAvailableGroups == 0) {
                $totalAvailableGroups = $totalDayTourGroups;
            }

            $dayTourInventory[$key] = [
                'is_available' => $totalAvailableGroups > 0,
                'min_quantity' => $totalAvailableGroups
            ];
        }

        // Get total day tour groups for fallback
        $totalDayTourQuantity = $totalDayTourGroups;

        return view('booking.widget', [
            'inventory'     => $inventory,
            'totalQuantity' => $totalQuantity,
            'dayTourInventory' => $dayTourInventory,
            'totalDayTourQuantity' => $totalDayTourQuantity,
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

            // Get dynamic max pets per room
            $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

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
                    'selected_rooms.*.num_pets' => ['nullable', 'integer', 'min:0', 'max:' . $maxPetsPerRoom],
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

                        if ($numPets < 1 || $numPets > $maxPetsPerRoom) {
                            return response()->json([
                                'success' => false,
                                'message' => "Please set number of pets (1-{$maxPetsPerRoom}) for room ID {$roomId}.",
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

                foreach ($selectedRooms as $roomKey => $room) {
                    // Handle composite keys for multiple instances (e.g., "5_2" for room 5, instance 2)
                    $originalKey = $roomKey;
                    $roomId = (int)$roomKey;
                    
                    // Check if this is a composite key (e.g., "5_2")
                    $instanceNum = 1;
                    if (is_string($roomKey) && str_contains($roomKey, '_')) {
                        $parts = explode('_', $roomKey);
                        $roomId = (int)$parts[0];
                        $instanceNum = (int)($parts[1] ?? 1);
                    }
                    
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
                    // IMPORTANT: pass real adults/children so pricing + pets are computed correctly
                    $adults = (int)($room['adults'] ?? 1);
                    $children = (int)($room['children'] ?? 0);
                    $extraMattresses = !($isBYOT || $isCarCamping) ? (int)($room['extra_mattress'] ?? 0) : 0;

                    // pets should be charged whenever pets=yes and num_pets>0
                    // do NOT hard-require pet_agreement for fee computation
                    $numPets = (int)($room['num_pets'] ?? 0);
                    $hasPets = (($room['pets'] ?? 'no') === 'yes') && ($numPets > 0);
                    
                    $pricingResult = $this->calculateCentralizedStaycationPrice(
                        $staycation,
                        $checkinDate,
                        $checkoutDate,
                        $adults,
                        $children,
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

                    $normalized[$originalKey] = [
                        'room_id'        => $roomId,
                        'accommodation_id' => $roomId,
                        'instance_num'   => $instanceNum,
                        'accommodation_price' => $accommodationPrice,
                        'adults'         => $adults,
                        'children'       => (int)($room['children'] ?? 0),
                        'extra_mattress' => (int)($room['extra_mattress'] ?? 0),
                        'quantity'       => ($isBYOT || $isCarCamping) ? max(1, (int)($room['extra_mattress'] ?? 1)) : ($adults + (int)($room['children'] ?? 0)),
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


            $validated = $request->validate(ValidationService::staycationPostRules());
            $booking = session('booking', []);

            // Validate that adults+children <= staycation.capacity for each accommodation
            if (isset($validated['accommodations']) && is_array($validated['accommodations'])) {
                foreach ($validated['accommodations'] as $acc) {
                    $staycation = isset($acc['staycation']['id'])
                        ? \App\Models\Staycation::find($acc['staycation']['id'])
                        : null;
                    $adults = $acc['adults'] ?? 0;
                    $children = $acc['children'] ?? 0;
                    if ($staycation && ($adults + $children > $staycation->capacity)) {
                        return back()->withErrors(["The total guests for {$staycation->name} exceed its capacity."])->withInput();
                    }
                }
            }

            if ($request->has('check_in') && $request->has('check_out')) {
                $newCheckin = $request->check_in;
                $newCheckout = $request->check_out;

                $oldCheckin = $booking['checkin'] ?? null;
                $oldCheckout = $booking['checkout'] ?? null;
                $datesChanged = ($oldCheckin !== $newCheckin) || ($oldCheckout !== $newCheckout);

                $booking['checkin'] = $newCheckin;
                $booking['checkout'] = $newCheckout;
                $booking['booking_type'] = 'staycation';
                $booking['completed_steps'] = [1];
                $booking['current_step'] = 1;
                $booking['timestamp'] = now()->timestamp;

                // Store rooms and guests data
                $booking['rooms_count'] = $request->input('rooms_count', 1);
                $booking['adults_total'] = $request->input('adults_total', 1);
                $booking['children_total'] = $request->input('children_total', 0);
                $booking['rooms'] = $request->input('rooms', [['adults' => 1, 'children' => 0]]);

                // Hard reset downstream selections on new date range
                if ($datesChanged) {
                    $booking['accommodations'] = [];
                    $booking['selected_foods'] = [];
                    $booking['selected_activities'] = [];
                    $booking['selected_activity_packages'] = [];
                    unset($booking['final_totals']);
                    session()->forget(['selected_foods', 'selected_activities', 'selected_activity_packages']);
                }

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

        // Handle intent parameter for dining inclusions
        if ($request->has('intent')) {
            $booking = session('booking', [
                'checkin'         => null,
                'checkout'        => null,
                'accommodations'  => [],
                'booking_type'    => 'staycation',
                'timestamp'       => now()->timestamp,
                'completed_steps' => [],
                'current_step'    => 1,
            ]);
            
            $booking['intent'] = $request->input('intent');
            session(['booking' => $booking]);
        }

        // For GET requests, delegate to the availability page builder
        return $this->staycationAvailability($request);
    }

    /**
     * Save booking form data to session (for calendar form selections)
     */
    public function saveBookingSession(Request $request)
    {
        $validated = $request->validate([
            'checkin' => 'required|date_format:Y-m-d',
            'checkout' => 'required|date_format:Y-m-d',
            'rooms_count' => 'required|integer|min:1|max:4',
            'adults_total' => 'required|integer|min:1',
            'children_total' => 'required|integer|min:0',
            'pets_total' => 'required|integer|min:0',
            'rooms' => 'required|array',
            'rooms.*.adults' => 'required|integer|min:1',
            'rooms.*.children' => 'required|integer|min:0',
            'rooms.*.pets' => 'integer|min:0',
        ]);

        // Store booking data in session
        session(['booking' => [
            'checkin' => $validated['checkin'],
            'checkout' => $validated['checkout'],
            'rooms_count' => $validated['rooms_count'],
            'adults_total' => $validated['adults_total'],
            'children_total' => $validated['children_total'],
            'pets_total' => $validated['pets_total'],
            'rooms' => $validated['rooms'],
        ]]);

        return response()->json(['success' => true, 'message' => 'Booking data saved to session']);
    }

    public function staycationAvailability(Request $request)
    {
        // Check for dates in query parameters first (from calendar), then session
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');

        if (!$checkin || !$checkout) {
            $booking = session('booking', []);
            $checkin = $booking['checkin'] ?? now()->format('Y-m-d');
            $checkout = $booking['checkout'] ?? now()->addDays(2)->format('Y-m-d');
        }

        // Store dates in session for subsequent requests
        // Reset selected accommodations when date range changes to avoid stale room/pet picks across different dates
        $booking = session('booking', []);
        $previousCheckin = $booking['checkin'] ?? null;
        $previousCheckout = $booking['checkout'] ?? null;
        $datesChanged = ($previousCheckin !== $checkin) || ($previousCheckout !== $checkout);

        if ($datesChanged) {
            $booking['accommodations'] = [];
            $booking['current_step'] = 1;

            // keep only step 1 as completed on new date selection
            $completed = $booking['completed_steps'] ?? [];
            $booking['completed_steps'] = in_array(1, $completed) ? [1] : [];
        }

        $booking['checkin'] = $checkin;
        $booking['checkout'] = $checkout;
        // Preserve dining intent & prefill when provided via query params (from dining modal)
        if ($request->has('intent')) {
            $booking['intent'] = $request->query('intent');
            // Prefill selected food info if provided
            if ($request->has('food_id')) {
                $selected = [
                    'food_id' => (int) $request->query('food_id'),
                    'meal_period' => $request->query('meal_period'),
                    'date' => $request->query('date'),
                    'time' => $request->query('time'),
                    'adults' => (int) ($request->query('adults') ?? ($booking['adults_total'] ?? 1)),
                    'first_name' => $request->query('first_name') ?? null,
                    'last_name' => $request->query('last_name') ?? null,
                    'email' => $request->query('email') ?? null,
                ];
                // Store both in booking array and session key used by food step
                $booking['selected_foods'] = [$selected];
                session(['selected_foods' => [$selected]]);
            }
        }
        session(['booking' => $booking]);

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
            $baseQty = max(0, (int)$staycation->base_quantity);
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
            // FIXED: Use the check-in date's specific season, not the overall range season
            $checkinSeasonType = $this->getSeasonTypeForDate($checkinCarbon);
            $checkinSeasonKey = $this->mapSeasonToKey($checkinSeasonType);
            $currentRate = $this->pricingService->getStaycationRate($staycation, $checkinCarbon, $checkinSeasonKey);

            // If no rate configured for the season, use staycation's price field
            if ($currentRate <= 0) {
                $currentRate = $staycation->price ?? 0;
            }

            // If no rate configured, use type-based default
            if ($currentRate <= 0) {
                $currentRate = $type === 'glamping' ? 5500 : 3000;
            }

            // Calculate price range for the selected date range
            // FIXED: No longer passing $seasonKey - calculatePriceRangeForDateRange now determines season per-day
            $priceRange = $this->calculatePriceRangeForDateRange($staycation, $checkin, $checkout);
            
            // Build date_pricing array for JavaScript
            $datePricing = [];
            foreach ($priceRange['prices'] ?? [] as $priceInfo) {
                $datePricing[$priceInfo['date']] = $priceInfo['rate'];
            }

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
                'price_configs' => $staycation->price_configs ?? [],
                'peak_periods' => $this->getPeakPeriodsForStaycation($staycation, $checkin, $checkout),
                'day_type_pricing' => $this->getDayTypePricingForStaycation($staycation),
                'date_pricing' => $datePricing // Date-specific pricing for JavaScript
            ];
        }

        // Get rooms/guests data from session (preserved from check-availability POST)
        $booking = session('booking', []);
        $roomsCount = $booking['rooms_count'] ?? 1;
        $adultsTotal = $booking['adults_total'] ?? 1;
        $childrenTotal = $booking['children_total'] ?? 0;
        $rooms = $booking['rooms'] ?? [['adults' => 1, 'children' => 0]];

        // Get dynamic max pets per room setting
        $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-availability', compact('staycations', 'availability', 'roomDetails', 'checkin', 'checkout', 'currentSeason', 'roomsCount', 'adultsTotal', 'childrenTotal', 'rooms', 'maxPetsPerRoom', 'vatRate', 'vatRatePercentage'));
    }

    /**
     * Get peak periods that apply to a staycation for the given date range
     */
    private function getPeakPeriodsForStaycation(Staycation $staycation, string $checkin, string $checkout)
    {
        $checkinCarbon = Carbon::parse($checkin);
        $checkoutCarbon = Carbon::parse($checkout);

        return PeakPeriod::where(function($query) use ($checkinCarbon, $checkoutCarbon) {
            $query->whereBetween('start_date', [$checkinCarbon->format('Y-m-d'), $checkoutCarbon->subDay()->format('Y-m-d')])
                  ->orWhereBetween('end_date', [$checkinCarbon->format('Y-m-d'), $checkoutCarbon->subDay()->format('Y-m-d')])
                  ->orWhere(function($q) use ($checkinCarbon, $checkoutCarbon) {
                      $q->where('start_date', '<=', $checkinCarbon->format('Y-m-d'))
                        ->where('end_date', '>=', $checkoutCarbon->subDay()->format('Y-m-d'));
                  });
        })
        ->with('peakType')
        ->get()
        ->map(function($period) {
            return [
                'id' => $period->id,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'peak_type_name' => $period->peakType->name,
                'name' => $period->name
            ];
        })
        ->toArray();
    }

    /**
     * Get day type pricing for a staycation
     * Calculates proper weekday/weekend pricing from the staycation's price_configs
     */
    private function getDayTypePricingForStaycation(Staycation $staycation)
    {
        $priceConfigs = $staycation->price_configs ?? [];
        $dayTypePricing = [];
        
        if (!empty($priceConfigs)) {
            $weekendDays = ['Sat', 'Sun'];
            $weekdayDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            
            $weekendRates = [];
            $weekdayRates = [];
            
            foreach ($priceConfigs as $config) {
                $rates = $config['rates'] ?? [];
                
                if (isset($config['days']) && is_array($config['days'])) {
                    $isWeekendConfig = false;
                    $isWeekdayConfig = false;
                    
                    foreach ($config['days'] as $day) {
                        if (in_array($day, $weekendDays)) {
                            $isWeekendConfig = true;
                        }
                        if (in_array($day, $weekdayDays)) {
                            $isWeekdayConfig = true;
                        }
                    }
                    
                    if ($isWeekendConfig) {
                        foreach (['nonPeak', 'peak', 'superPeak'] as $season) {
                            if (isset($rates[$season]) && $rates[$season] > 0) {
                                $key = $season . '_weekend';
                                $weekendRates[$key] = (float)$rates[$season];
                            }
                        }
                    }
                    
                    if ($isWeekdayConfig) {
                        foreach (['nonPeak', 'peak', 'superPeak'] as $season) {
                            if (isset($rates[$season]) && $rates[$season] > 0) {
                                $key = $season . '_weekday';
                                $weekdayRates[$key] = (float)$rates[$season];
                            }
                        }
                    }
                }
            }
            
            foreach (['nonPeak', 'peak', 'superPeak'] as $season) {
                $weekendKey = $season . '_weekend';
                $weekdayKey = $season . '_weekday';
                
                if (isset($weekendRates[$weekendKey])) {
                    $dayTypePricing[$weekendKey] = $weekendRates[$weekendKey];
                }
                if (isset($weekdayRates[$weekdayKey])) {
                    $dayTypePricing[$weekdayKey] = $weekdayRates[$weekdayKey];
                }
            }
            
            if (!empty($dayTypePricing)) {
                Log::info('Day type pricing from price_configs for ' . $staycation->name . '.', $dayTypePricing);
                return $dayTypePricing;
            }
        }
        
        // Fallback to PricingConfig table
        $unitType = strpos(strtolower($staycation->name), 'glamping') !== false ? 'glamping' : 'camping';
        
        $pricingConfigs = PricingConfig::where('unit_type', $unitType)
            ->with(['peakType', 'dayType'])
            ->get();
        
        foreach ($pricingConfigs as $config) {
            $seasonName = $config->peakType->name;
            $dayTypeName = $config->dayType->name;
            $key = strtolower($seasonName) . '_' . strtolower($dayTypeName);
            $dayTypePricing[$key] = $config->price;
        }
        
        Log::info('Day type pricing from PricingConfig for ' . $staycation->name . '.', $dayTypePricing);
        
        return $dayTypePricing;
    }

    public function staycationFood()
    {
        $booking = session('booking', []);
        // Ensure rooms were selected first
        if (empty($booking['accommodations'])) {
            return redirect()->route('booking.staycationAvailability')
                ->with('error', 'Please select your room(s) first.');
        }

        // Preserve intent from session or request
        if (!isset($booking['intent']) && request()->has('intent')) {
            $booking['intent'] = request()->input('intent');
        }

        // Prefer any selected_foods already saved in session (e.g. from dining prefill).
        // If none in session, fall back to booking['selected_foods'] if present.
        $sessionSelected = session('selected_foods', []);
        if (!empty($sessionSelected)) {
            $booking['selected_foods'] = $sessionSelected;
        } elseif (isset($booking['selected_foods']) && !empty($booking['selected_foods'])) {
            // ensure session has a copy as well
            session(['selected_foods' => $booking['selected_foods']]);
        } else {
            // No selected foods present — keep any existing session value untouched (don't forcibly clear)
            $booking['selected_foods'] = $sessionSelected ?: [];
        }

        session(['booking' => $booking]);

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

        // Fetch food packages for staycation
        $foodPackages = FoodPackage::with(['foods' => function($q) {
            $q->select('foods.id', 'foods.name', 'foods.price', 'foods.category');
        }])
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Fetch food packages section settings from admin
        $packagesSettings = [
            'show' => (bool)FoodPackageSetting::getValue('show', 1),
            'title' => FoodPackageSetting::getValue('title') ?? 'Meal Packages (Special Bundles)',
            'description' => FoodPackageSetting::getValue('description') ?? 'Curated meal bundles with special pricing',
            'icon' => FoodPackageSetting::getValue('icon') ?? 'bi-bag-heart'
        ];

        // Fetch staycation data for display in summary (array keys are room IDs)
        $accommodationIds = array_keys($booking['accommodations'] ?? []);
        $staycations = Staycation::whereIn('id', $accommodationIds)->get();

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-food', compact('booking', 'foodMenus', 'foodPackages', 'staycations', 'packagesSettings', 'vatRate', 'vatRatePercentage'));
    }

    public function saveFoodSelection(Request $request)
    {
        try {
            // Simple validation - just ensure selected_foods is an array
            $validated = $request->validate([
                'selected_foods' => 'required|array|min:1',
            ]);

            // Get the actual data without strict field validation
            $selectedFoods = $request->input('selected_foods', []);
            
            // Validate each food item has required fields
            foreach ($selectedFoods as $index => $food) {
                if (empty($food['id']) || empty($food['name']) || empty($food['category']) || !isset($food['price']) || !isset($food['quantity'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid food item data at index ' . $index
                    ], 422);
                }
            }

            // Store selected foods in session (legacy key)
            session(['selected_foods' => $selectedFoods]);

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
            $booking['selected_foods'] = $selectedFoods;

            // Calculate food total from selected foods
            $foodTotal = 0;
            foreach ($selectedFoods as $foodData) {
                $foodTotal += ($foodData['price'] ?? 0) * ($foodData['quantity'] ?? 1);
            }
            
            // Calculate accommodation total from current accommodations if available
            $accommodationTotal = 0;
            if (!empty($booking['accommodations'])) {
                foreach ($booking['accommodations'] as $acc) {
                    if (!empty($acc)) {
                        $accommodationTotal += $acc['total_price'] ?? 0;
                    }
                }
            }
            
            // Update final_totals to include food cost
            if (!isset($booking['final_totals'])) {
                $booking['final_totals'] = [
                    'accommodation_total' => $accommodationTotal,
                    'activities_total' => 0,
                    'food_total' => $foodTotal,
                    'grand_total' => $accommodationTotal + $foodTotal
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
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
            $isAvailable = $staycation->base_quantity > 0;

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

    private function checkAvailabilityInternal(string $checkin, string $checkout, array $staycationIds)
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



    private function calculatePrice(\App\Models\Staycation $staycation, string $date, int $guests = 2)
    // If $staycation is always a model, add the correct type hint. Assuming Staycation model:
    // private function calculatePrice(Staycation $staycation, string $date, int $guests = 2)
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

                    // For staycations, price is fixed regardless of guests.
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
    private function determineDayTypeForDate(string $date)
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
    private function determineSeasonTypeForDate(string $date)
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
        ]);
    }

    /**
     * Calculate mattress fee for a given quantity and number of nights
     * Centralized method to avoid repeating the calculation
     */
    protected function calculateMattressFee(int $mattressCount, int $nights)
    {
        $pricePerNight = BookingSetting::getValue('mattress_price_per_night', 0);
        return (int)$mattressCount * (int)$pricePerNight * max(1, $nights);
    }

    /**
     * Calculate pet fee for a given pet count
     * Centralized method to avoid repeating the calculation
     */
    protected function calculatePetFee(int $petCount)
    {
        $petPrice = BookingSetting::getValue('pet_price', 0);
        return (int)$petCount * (int)$petPrice;
    }
}
