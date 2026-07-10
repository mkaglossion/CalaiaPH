<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
use App\Models\Booking;
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
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\PricingConfig;
use App\Models\StaycationInventory;
use Carbon\Carbon;
use App\Services\PricingService;
use App\Services\BookingConfirmationContentService;
use App\Models\BookingSetting;
use App\Models\FoodPackageSetting;
use Barryvdh\DomPDF\Facade\Pdf;



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
     * @param int $extraMattresses Legacy input field (kept for compatibility)
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

        // Determine room type behavior
        $isSpecialRoom = $this->isSpecialStaycationRoom($staycation);
        $supportsExtraMattress = $this->supportsExtraMattress($staycation);

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
        $additionalAdults = 0;
        $petPrice = 0;

        // Additional adult fee (legacy: mattress_price key) is GLAMPING-only.
        if ($supportsExtraMattress) {
            $additionalAdults = $this->resolveAdditionalAdultCount($staycation, $adults);
            $mattressPrice = $this->calculateAdditionalAdultFee($additionalAdults, $nights);
        }
        if (!$isSpecialRoom) {
            if ($hasPets && $numPets > 0) {
                $petPrice = $this->calculatePetFee($numPets);
            }
        }

        $totalPrice = $accommodationPrice + $mattressPrice + $petPrice;

        return [
            'accommodation_price' => $accommodationPrice,
            'mattress_price' => $mattressPrice,
            'additional_adults' => $additionalAdults,
            'additional_adult_price' => $mattressPrice,
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

    /**
     * Build a canonical acceptance record for staycation checkout.
     *
     * Keeping the acceptances in the booking payload lets the confirmation email
     * and admin views show exactly what the guest agreed to.
     */
    private function buildStaycationAcceptanceData(Request $request): array
    {
        $acceptedAt = now()->toDateTimeString();
        $termsAccepted = $request->boolean('terms_and_policy_accepted') || $request->boolean('terms_accepted');
        $waiverDetails = $this->buildCampWaiverSubmissionData($request);

        return [
            'terms_and_policy' => [
                'accepted' => $termsAccepted,
                'accepted_at' => $termsAccepted ? $acceptedAt : null,
                'label' => 'Terms & Conditions and Cancellation Policy',
                'source' => $request->boolean('terms_and_policy_accepted') ? 'terms_and_policy_accepted' : 'terms_accepted',
                'version' => 'staycation-terms-v1',
            ],
            'camp_waiver' => [
                'accepted' => $request->boolean('camp_waiver_accepted'),
                'accepted_at' => $request->boolean('camp_waiver_accepted') ? $acceptedAt : null,
                'label' => 'Camp Waiver and Release from Liability',
                'source' => 'camp_waiver_accepted',
                'version' => 'camp-waiver-v1',
                'details' => $waiverDetails,
            ],
        ];
    }

    private function campWaiverRules(): array
    {
        return [
            'camp_waiver_participant_name' => 'required|string|max:255',
            'camp_waiver_birth_date' => 'required|date',
            'camp_waiver_gender' => 'required|string|in:male,female,other,prefer_not_to_say',
            'camp_waiver_address' => 'required|string|max:500',
            'camp_waiver_parent_guardian_name' => 'nullable|string|max:255',
            'camp_waiver_emergency_name' => 'required|string|max:255',
            'camp_waiver_emergency_phone' => 'required|string|max:20',
            'camp_waiver_relationship' => 'required|string|max:255',
            'camp_waiver_medical_condition' => 'nullable|string|max:1000',
            'camp_waiver_signature_name' => 'required|string|max:255',
            'camp_waiver_signature_date' => 'required|date',
            'camp_waiver_parent_signature_name' => 'nullable|string|max:255',
            'camp_waiver_child_ward_name' => 'nullable|string|max:255',
            'camp_waiver_parent_signature_date' => 'nullable|date',
        ];
    }

    private function buildCampWaiverSubmissionData(Request $request): array
    {
        $fields = [
            'participant_name' => 'camp_waiver_participant_name',
            'birth_date' => 'camp_waiver_birth_date',
            'gender' => 'camp_waiver_gender',
            'address' => 'camp_waiver_address',
            'parent_guardian_name' => 'camp_waiver_parent_guardian_name',
            'emergency_contact_name' => 'camp_waiver_emergency_name',
            'emergency_contact_phone' => 'camp_waiver_emergency_phone',
            'relationship_to_participant' => 'camp_waiver_relationship',
            'medical_condition' => 'camp_waiver_medical_condition',
            'signature_name' => 'camp_waiver_signature_name',
            'signature_date' => 'camp_waiver_signature_date',
            'parent_signature_name' => 'camp_waiver_parent_signature_name',
            'child_ward_name' => 'camp_waiver_child_ward_name',
            'parent_signature_date' => 'camp_waiver_parent_signature_date',
        ];

        $submission = [];

        foreach ($fields as $key => $inputKey) {
            $value = trim((string) $request->input($inputKey, ''));
            $submission[$key] = $value !== '' ? $value : null;
        }

        return $submission;
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
            'children' => 'nullable|integer|min:0',
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
            
            $adults = (int) $request->input('adults', 1);
            $children = (int) $request->input('children', 0);
            $totalPrice = ($adults * $adultPrice) + ($children * $childrenPrice);

            return response()->json([
                'success' => true,
                'total' => round($totalPrice, 2),
                'breakdown' => [
                    'adults' => $adults,
                    'children' => $children,
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
            'children' => 'nullable|integer|min:0',
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
            $adults = (int) $request->input('adults', 1);
            $children = (int) $request->input('children', 0);
            $pets = (int) $request->input('pets', 0);
            $priceResult = $this->calculateCentralizedStaycationPrice(
                $staycation,
                $checkin,
                $checkout,
                $adults,
                $children,
                0, // extra_mattresses - not needed for calendar preview
                $pets,
                $pets > 0
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
            'children' => 'nullable|integer|min:0',
            'extra_mattresses' => 'nullable|integer|min:0',
            'num_pets' => 'required|integer|min:0',
            'has_pets' => 'required|boolean',
        ]);

        $staycation = Staycation::find($request->staycation_id);
        if (!$staycation) {
            return response()->json(['success' => false, 'message' => 'Staycation not found'], 404);
        }

        $adults = (int) $request->input('adults', 1);
        $children = (int) $request->input('children', 0);
        $pricingResult = $this->calculateCentralizedStaycationPrice(
            $staycation,
            $request->checkin_date,
            $request->checkout_date,
            $adults,
            $children,
            (int)($request->extra_mattresses ?? 0),
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
            'selectedActivityPackages' => session('selected_activity_packages', []),
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

        $contentType = 'dayvisit';
        if ($type === 'staycation') {
            $contentType = 'staycation';
        }

        $data['confirmationContent'] = app(BookingConfirmationContentService::class)->forType($contentType, [
            'reference' => $bookingData['transaction_id'] ?? '',
            'package' => $bookingData['package'] ?? '',
        ]);

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

    public function staycationActivities(Request $request)
    {
        $sortBy = $request->string('sort_by', 'featured')->toString();
        $sortOptions = [
            'featured' => 'Featured',
            'name_asc' => 'Alphabetically, A-Z',
            'name_desc' => 'Alphabetically, Z-A',
            'price_asc' => 'Price, low to high',
            'price_desc' => 'Price, high to low',
            'oldest' => 'Date, old to new',
            'newest' => 'Date, new to old',
        ];
        if (!array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'featured';
        }

        // Fetch staycations from the database
        $staycations = Staycation::ordered()->get()->keyBy('id'); // Key by ID for proper lookup

        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete accommodation selection first.');
        }

        // Fetch activities
        $activitiesQuery = Activity::where('is_active', true);
        switch ($sortBy) {
            case 'name_asc':
                $activitiesQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $activitiesQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $activitiesQuery->orderBy('price', 'asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $activitiesQuery->orderBy('price', 'desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $activitiesQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $activitiesQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $activitiesQuery->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
                break;
        }
        $activities = $activitiesQuery->get();
        
        // Fetch activity packages with their activities
        $activityPackagesQuery = ActivityPackage::where('is_active', true)
            ->with(['activities' => function($q) {
                $q->select('activities.id', 'activities.name', 'activities.price')
                  ->orderBy('activities.name');
            }]);

        switch ($sortBy) {
            case 'name_asc':
                $activityPackagesQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $activityPackagesQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $activityPackagesQuery->orderByRaw('COALESCE(price, 0) asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $activityPackagesQuery->orderByRaw('COALESCE(price, 0) desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $activityPackagesQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $activityPackagesQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $activityPackagesQuery->latest();
                break;
        }
        $activityPackages = $activityPackagesQuery->get();
        
        // Get selected foods from session
        $selectedFoods = $this->normalizeStaycationFoods($booking['selected_foods'] ?? session('selected_foods', []));

        // Fetch food details if we have selected foods
        $foodIds = [];
        if (!empty($selectedFoods)) {
            $foodIds = array_values(array_filter(array_map(function ($item) {
                $id = $item['id'] ?? null;
                return is_numeric($id) ? (int) $id : null;
            }, $selectedFoods)));
        }
        $foods = $foodIds ? \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id') : collect();

        // Get selected activities from session
        $selectedActivities = $booking['selected_activities'] ?? session('selected_activities', []);
        $selectedActivityPackages = $booking['selected_activity_packages'] ?? session('selected_activity_packages', []);

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-activities', compact('booking', 'activities', 'activityPackages', 'staycations', 'selectedFoods', 'foods', 'selectedActivities', 'selectedActivityPackages', 'vatRate', 'vatRatePercentage', 'sortBy', 'sortOptions'));
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
            ]);

            $selectedActivities = $validated['selected_activities'] ?? [];
            [$activities, $activityPackages] = $this->normalizeStaycationActivities($selectedActivities);

            if (!empty($selectedActivities) && empty($activities) && empty($activityPackages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid activity selection.',
                ], 422);
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

        $booking = $this->normalizeAndRecalculateStaycationBooking($booking);
        
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
            [$adults, $children] = $this->clampRoomGuestCounts($staycation, $adults, $children);
            $acc['adults'] = $adults;
            $acc['children'] = $children;
            $numPets = (int)($acc['num_pets'] ?? 0);
            $hasPets = (($acc['pets'] ?? 'no') === 'yes') && ($numPets > 0);
            $extraMattresses = $this->resolveExtraMattressCount(
                $staycation,
                $adults,
                $children,
                (int)($acc['extra_mattress'] ?? 0)
            );
            
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
            
            // Update accommodation with calculated prices
            $acc['accommodation_price'] = $pricingResult['accommodation_price'];
            $acc['extra_mattress'] = $extraMattresses;
            $acc['mattress_price'] = $pricingResult['mattress_price'];
            $acc['additional_adults'] = $pricingResult['additional_adults'] ?? $extraMattresses;
            $acc['additional_adult_price'] = $pricingResult['additional_adult_price'] ?? $pricingResult['mattress_price'];
            $acc['pet_price'] = $pricingResult['pet_price'];
            $acc['total_price'] = $pricingResult['total_price'];
            
            $total += $pricingResult['total_price'];
        }

        return $total;
    }

    private function clampInt(mixed $value, int $min, int $max): int
    {
        $number = (int) $value;
        if ($number < $min) {
            return $min;
        }
        if ($number > $max) {
            return $max;
        }
        return $number;
    }

    private function extractNumericId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value)) {
            if (preg_match('/(\d+)/', $value, $matches)) {
                $id = (int) $matches[1];
                return $id > 0 ? $id : null;
            }
        }

        if (is_numeric($value)) {
            $id = (int) $value;
            return $id > 0 ? $id : null;
        }

        return null;
    }

    private function normalizeStaycationFoods(array $selectedFoods): array
    {
        $foodIds = [];
        $packageIds = [];

        foreach ($selectedFoods as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rawId = $item['id'] ?? null;
            $isPackage = ($item['category'] ?? null) === 'package'
                || isset($item['package_id'])
                || (is_string($rawId) && str_starts_with($rawId, 'package-'));

            if ($isPackage) {
                $packageId = $this->extractNumericId($item['package_id'] ?? $rawId);
                if ($packageId) {
                    $packageIds[$packageId] = $packageId;
                }
                continue;
            }

            $foodId = $this->extractNumericId($rawId);
            if ($foodId) {
                $foodIds[$foodId] = $foodId;
            }
        }

        $foods = Food::whereIn('id', array_values($foodIds))->get()->keyBy('id');
        $foodPackages = FoodPackage::whereIn('id', array_values($packageIds))->get()->keyBy('id');

        $normalized = [];
        foreach ($selectedFoods as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rawId = $item['id'] ?? null;
            $isPackage = ($item['category'] ?? null) === 'package'
                || isset($item['package_id'])
                || (is_string($rawId) && str_starts_with($rawId, 'package-'));
            $quantity = $this->clampInt($item['quantity'] ?? 1, 1, 20);
            $notes = trim((string) ($item['notes'] ?? ''));

            if ($isPackage) {
                $packageId = $this->extractNumericId($item['package_id'] ?? $rawId);
                if (!$packageId || !isset($foodPackages[$packageId])) {
                    continue;
                }

                $foodPackage = $foodPackages[$packageId];
                $includedFoods = $item['included_foods'] ?? [];
                $packageSections = $item['package_sections'] ?? ($foodPackage->sections ?? []);
                $selectedChoices = $item['selected_choices'] ?? [];
                
                // Build included_foods from package_sections if not already present
                if (empty($includedFoods) && !empty($packageSections) && is_array($packageSections)) {
                    $includedFoods = $this->buildPackageIncludedFoods($packageSections, $selectedChoices);
                }
                
                $normalized[] = [
                    'id' => 'package-' . $foodPackage->id,
                    'package_id' => (string) $foodPackage->id,
                    'name' => $foodPackage->name,
                    'category' => 'package',
                    'price' => (float) $foodPackage->getEffectivePrice(),
                    'quantity' => $quantity,
                    'notes' => $notes,
                    'is_package' => true,
                    'included_foods' => is_array($includedFoods) ? $includedFoods : [],
                    'package_sections' => is_array($packageSections) ? $packageSections : [],
                    'selected_choices' => is_array($selectedChoices) ? $selectedChoices : [],
                ];
                continue;
            }

            $foodId = $this->extractNumericId($rawId);
            if (!$foodId || !isset($foods[$foodId])) {
                continue;
            }

            $food = $foods[$foodId];
            $normalized[] = [
                'id' => (int) $food->id,
                'name' => $food->name,
                'category' => $food->category ?? ($item['category'] ?? 'messhall'),
                'price' => (float) $food->price,
                'quantity' => $quantity,
                'notes' => $notes,
            ];
        }

        return $normalized;
    }

    /**
     * Build included_foods array from package_sections for display
     * Handles both pre-converted items (with id/name/price) and raw DB items (with food_id)
     */
    private function buildPackageIncludedFoods(array $packageSections, array $selectedChoices = []): array
    {
        $includedFoods = [];
        
        foreach ($packageSections as $sectionIndex => $section) {
            $mode = $section['mode'] ?? 'included';
            $sectionTitle = $section['title'] ?? '';

            if ($mode === 'choice') {
                // For choice sections, look up the selected choice
                $lookupKey = (string) ($sectionTitle ?: $sectionIndex);
                $selectedChoice = collect($selectedChoices)->first(function ($choice) use ($lookupKey) {
                    return (string) ($choice['section_title'] ?? $choice['section_index'] ?? '') === $lookupKey;
                });
                
                if ($selectedChoice) {
                    $includedFoods[] = [
                        'id' => $selectedChoice['food_id'] ?? null,
                        'name' => $selectedChoice['food_name'] ?? 'Unknown',
                        'price' => $selectedChoice['price'] ?? 0,
                        'quantity' => 1,
                        'section_title' => $sectionTitle,
                    ];
                }
            } else {
                // For included sections, process all items
                foreach ($section['items'] ?? [] as $item) {
                    // Handle both pre-converted (id) and raw DB (food_id) formats
                    $foodId = $item['id'] ?? $item['food_id'] ?? null;
                    $foodName = $item['name'] ?? $item['food_name'] ?? 'Unknown';
                    $foodPrice = $item['price'] ?? $item['food_price'] ?? 0;
                    
                    $includedFoods[] = [
                        'id' => $foodId,
                        'name' => $foodName,
                        'price' => (float) $foodPrice,
                        'quantity' => $item['quantity'] ?? 1,
                        'section_title' => $sectionTitle,
                    ];
                }
            }
        }

        return array_values(array_filter($includedFoods, fn($item) => !empty($item['id'])));
    }

    private function normalizeStaycationActivities(array $selectedItems): array
    {
        $activityIds = [];
        $packageIds = [];

        foreach ($selectedItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rawId = $item['id'] ?? null;
            $isPackage = (bool) ($item['is_package'] ?? false)
                || isset($item['package_id'])
                || (is_string($rawId) && str_starts_with($rawId, 'package-'));

            if ($isPackage) {
                $packageId = $this->extractNumericId($item['package_id'] ?? $rawId);
                if ($packageId) {
                    $packageIds[$packageId] = $packageId;
                }
                continue;
            }

            $activityId = $this->extractNumericId($rawId);
            if ($activityId) {
                $activityIds[$activityId] = $activityId;
            }
        }

        $activities = Activity::whereIn('id', array_values($activityIds))->get()->keyBy('id');
        $activityPackages = ActivityPackage::with(['activities:id,name'])
            ->whereIn('id', array_values($packageIds))
            ->get()
            ->keyBy('id');

        $normalizedActivities = [];
        $normalizedPackages = [];

        foreach ($selectedItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $rawId = $item['id'] ?? null;
            $isPackage = (bool) ($item['is_package'] ?? false)
                || isset($item['package_id'])
                || (is_string($rawId) && str_starts_with($rawId, 'package-'));

            if ($isPackage) {
                $packageId = $this->extractNumericId($item['package_id'] ?? $rawId);
                if (!$packageId || !isset($activityPackages[$packageId])) {
                    continue;
                }

                $package = $activityPackages[$packageId];
                $quantity = $this->clampInt($item['quantity'] ?? 1, 1, 20);
                $includedActivities = $package->activities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'quantity' => (int) ($activity->pivot->quantity ?? 1),
                    ];
                })->values()->all();

                $normalizedPackages[] = [
                    'id' => 'package-' . $package->id,
                    'package_id' => (string) $package->id,
                    'name' => $package->name,
                    'category' => $package->category ?? 'package',
                    'price' => (float) $package->getEffectivePrice(),
                    'quantity' => $quantity,
                    'is_package' => true,
                    'included_activities' => $includedActivities,
                ];
                continue;
            }

            $activityId = $this->extractNumericId($rawId);
            if (!$activityId || !isset($activities[$activityId])) {
                continue;
            }

            $activity = $activities[$activityId];
            $guests = $this->clampInt($item['guests'] ?? $item['quantity'] ?? 1, 1, 20);
            $normalizedActivities[] = [
                'id' => (string) $activity->id,
                'name' => $activity->name,
                'category' => $activity->category ?? ($item['category'] ?? 'activity'),
                'price' => (float) $activity->price,
                'guests' => $guests,
            ];
        }

        return [$normalizedActivities, $normalizedPackages];
    }

    private function normalizeAndRecalculateStaycationBooking(array $booking): array
    {
        $checkinDate = $booking['checkin'] ?? null;
        $checkoutDate = $booking['checkout'] ?? null;
        $datesSelected = !empty($checkinDate) && !empty($checkoutDate);

        if ($datesSelected && !empty($booking['accommodations'])) {
            $accommodations = $booking['accommodations'];
            $accommodationTotal = $this->calculateStaycationTotal($accommodations, $checkinDate, $checkoutDate);
            $booking['accommodations'] = $accommodations;
        } else {
            $accommodationTotal = collect($booking['accommodations'] ?? [])->sum(fn ($acc) => (float) ($acc['total_price'] ?? 0));
        }

        $booking['selected_foods'] = $this->normalizeStaycationFoods($booking['selected_foods'] ?? []);

        $selectedActivitiesRaw = array_merge(
            $booking['selected_activities'] ?? [],
            $booking['selected_activity_packages'] ?? []
        );
        [$normalizedActivities, $normalizedPackages] = $this->normalizeStaycationActivities($selectedActivitiesRaw);
        $booking['selected_activities'] = $normalizedActivities;
        $booking['selected_activity_packages'] = $normalizedPackages;

        $foodTotal = collect($booking['selected_foods'] ?? [])->sum(function ($food) {
            return ((float) ($food['price'] ?? 0)) * ((int) ($food['quantity'] ?? 1));
        });

        $activitiesTotal = collect($booking['selected_activities'] ?? [])->sum(function ($activity) {
            return ((float) ($activity['price'] ?? 0)) * ((int) ($activity['guests'] ?? 1));
        });

        $activitiesTotal += collect($booking['selected_activity_packages'] ?? [])->sum(function ($package) {
            return ((float) ($package['price'] ?? 0)) * ((int) ($package['quantity'] ?? 1));
        });

        $booking['final_totals'] = [
            'accommodation_total' => $accommodationTotal,
            'activities_total' => $activitiesTotal,
            'food_total' => $foodTotal,
            'grand_total' => $accommodationTotal + $activitiesTotal + $foodTotal,
        ];

        return $booking;
    }

    private function getValidatedPromoDiscount(?int $promoId, ?string $promoCode, float $bookingTotal): array
    {
        if (!$promoId || empty($promoCode)) {
            return ['discount' => 0.0, 'promo' => null];
        }

        \App\Models\PromoCode::syncAutomaticStatus();

        $promo = \App\Models\PromoCode::find($promoId);
        if (!$promo || strtoupper((string) $promo->code) !== strtoupper(trim($promoCode))) {
            return ['discount' => 0.0, 'promo' => null];
        }

        $today = today();
        if (!$promo->is_active || $today->lt($promo->start_date) || $today->gt($promo->end_date)) {
            return ['discount' => 0.0, 'promo' => null];
        }

        if ($promo->min_booking_amount && $bookingTotal < (float) $promo->min_booking_amount) {
            return ['discount' => 0.0, 'promo' => null];
        }

        if ($promo->usage_limit && $promo->usage_count >= $promo->usage_limit) {
            return ['discount' => 0.0, 'promo' => null];
        }

        $discount = 0.0;
        if ($promo->discount_type === 'percentage') {
            $discount = $bookingTotal * ((float) $promo->discount_value / 100);
        } else {
            $discount = min((float) $promo->discount_value, $bookingTotal);
        }

        return ['discount' => max(0.0, $discount), 'promo' => $promo];
    }

    public function staycationPayment()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete your staycation booking first.');
        }

        $checkinDate = $booking['checkin'];
        $checkoutDate = $booking['checkout'];
        $booking = $this->normalizeAndRecalculateStaycationBooking($booking);
        
        session(['booking' => $booking]);

        // Get selected foods from current booking session only
        $selectedFoods = $booking['selected_foods'] ?? [];

        // Fetch food details if we have selected foods
        $foodIds = [];
        if (!empty($selectedFoods)) {
            $foodIds = array_values(array_filter(array_map(function ($item) {
                $id = $item['id'] ?? null;
                return is_numeric($id) ? (int) $id : null;
            }, $selectedFoods)));
        }
        $foods = $foodIds ? \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id') : collect();

        // Fetch staycations for room names
        $staycations = \App\Models\Staycation::ordered()->get()->keyBy('id');

        // Get selected activities and activity packages from session
        $selectedActivities = $booking['selected_activities'] ?? [];
        $selectedActivityPackages = $booking['selected_activity_packages'] ?? [];

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();
        $confirmationContent = app(BookingConfirmationContentService::class)->forType('staycation', [
            'reference' => $booking['transaction_id'] ?? '',
            'package' => $booking['package'] ?? 'Staycation',
        ]);
        $campWaiverContent = app(BookingConfirmationContentService::class)->forType('campwaiver', [
            'reference' => $booking['transaction_id'] ?? '',
            'package' => $booking['package'] ?? 'Staycation',
        ]);

        return view('booking.staycation-payment', compact('booking', 'selectedFoods', 'foods', 'staycations', 'selectedActivities', 'selectedActivityPackages', 'vatRate', 'vatRatePercentage', 'confirmationContent', 'campWaiverContent'));
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
            $staycations = Staycation::ordered()->get()->keyBy('id');
            $confirmationContent = app(BookingConfirmationContentService::class)->forType('staycation', [
                'reference' => $staycationBooking['transaction_id'] ?? '',
                'package' => $staycationBooking['package'] ?? 'Staycation',
            ]);
            
            $data = [
                'bookingData' => $staycationBooking,
                'selectedActivities' => $staycationBooking['selected_activities'] ?? [],
                'selectedActivityPackages' => $staycationBooking['selected_activity_packages'] ?? [],
                'selectedFoods' => $staycationBooking['selected_foods'] ?? [],
                'foods' => $foods,
                'staycations' => $staycations,
                'grandTotal' => $staycationBooking['final_totals']['grand_total'] ?? 0,
                'customerInfo' => session('staycation_customer_info', []),
                'isStaycation' => true,
                'confirmationContent' => $confirmationContent,
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

    public function campWaiver(string $transactionId)
    {
        $payload = $this->resolveCampWaiverPayload($transactionId);

        return view('booking.camp-waiver', $payload);
    }

    public function campWaiverPdf(string $transactionId)
    {
        $payload = $this->resolveCampWaiverPayload($transactionId);
        $filename = 'camp-waiver-' . $payload['booking']->transaction_id . '.pdf';

        return Pdf::loadView('booking.camp-waiver-pdf', $payload)
            ->setPaper('legal', 'portrait')
            ->download($filename);
    }

    private function resolveCampWaiverPayload(string $transactionId): array
    {
        $booking = Booking::where('transaction_id', $transactionId)->first();

        if (!$booking) {
            if ($this->isCampWaiverPreviewTransactionId($transactionId)) {
                return $this->campWaiverPreviewPayload($transactionId);
            }

            abort(404);
        }

        abort_unless(in_array($booking->booking_type, Booking::STAYCATION_TYPES, true), 404);

        $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
        $acceptances = $bookingData['acceptances'] ?? [];
        $campWaiverAcceptance = collect($acceptances)->first(function ($agreement) {
            return ($agreement['source'] ?? '') === 'camp_waiver_accepted'
                || strcasecmp((string) ($agreement['label'] ?? ''), 'Camp Waiver and Release from Liability') === 0;
        });

        $confirmationContent = app(BookingConfirmationContentService::class)->forType('campwaiver', [
            'reference' => $booking->transaction_id ?? '',
            'package' => $bookingData['package'] ?? 'Staycation',
        ]);

        $customerInfo = $bookingData['customer_info'] ?? [];
        if (empty($customerInfo)) {
            $customerParts = preg_split('/\s+/', trim((string) $booking->customer_name), 2) ?: [];
            $customerInfo = [
                'first_name' => $customerParts[0] ?? '',
                'last_name' => $customerParts[1] ?? '',
                'email' => $booking->customer_email ?? '',
                'phone' => $booking->customer_phone ?? '',
            ];
        }

        return [
            'booking' => $booking,
            'bookingData' => $bookingData,
            'customerInfo' => $customerInfo,
            'campWaiverAcceptance' => $campWaiverAcceptance,
            'confirmationContent' => $confirmationContent,
            'waiverContent' => $confirmationContent['camp_waiver'] ?? [],
        ];
    }

    private function isCampWaiverPreviewTransactionId(string $transactionId): bool
    {
        return str_starts_with($transactionId, 'PREVIEW-');
    }

    private function campWaiverPreviewPayload(string $transactionId): array
    {
        $booking = new Booking([
            'transaction_id' => $transactionId,
            'customer_name' => 'Juan Dela Cruz',
            'customer_email' => 'juan@example.com',
            'customer_phone' => '+63 900 000 0000',
            'booking_type' => 'staycation',
            'booking_data' => [
                'transaction_id' => $transactionId,
                'package' => 'Staycation',
                'customer_info' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'email' => 'juan@example.com',
                    'phone' => '+63 900 000 0000',
                ],
                'acceptances' => [[
                    'source' => 'camp_waiver_accepted',
                    'label' => 'Camp Waiver and Release from Liability',
                    'accepted' => true,
                    'accepted_at' => now()->toDateTimeString(),
                    'details' => [
                        'participant_name' => 'Juan Dela Cruz',
                        'signature_name' => 'Juan Dela Cruz',
                        'signature_date' => now()->toDateString(),
                    ],
                ]],
            ],
        ]);
        $booking->exists = true;

        $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
        $confirmationContent = app(BookingConfirmationContentService::class)->forType('campwaiver', [
            'reference' => $transactionId,
            'package' => 'Staycation',
        ]);

        return [
            'booking' => $booking,
            'bookingData' => $bookingData,
            'customerInfo' => $bookingData['customer_info'] ?? [],
            'campWaiverAcceptance' => collect($bookingData['acceptances'] ?? [])->first(),
            'confirmationContent' => $confirmationContent,
            'waiverContent' => $confirmationContent['camp_waiver'] ?? [],
        ];
    }


    public function processPayment(Request $request)
    {
        // Validate payment form
        $request->validate(ValidationService::paymentRules());
        if (!$request->boolean('terms_accepted') && !$request->boolean('terms_and_policy_accepted')) {
            return redirect()->back()
                ->withErrors(['terms_and_policy_accepted' => 'You must accept the booking terms and policy.'])
                ->withInput();
        }

        if (!empty(session('booking', [])) && (session('booking.booking_type') === 'staycation')) {
            if (!$request->boolean('camp_waiver_accepted')) {
                return redirect()->back()
                    ->withErrors(['camp_waiver_accepted' => 'You must accept the camp waiver before continuing.'])
                    ->withInput();
            }

            $request->validate(array_merge($this->campWaiverRules(), [
                'camp_waiver_accepted' => 'accepted',
            ]));
        }

        // Use only trusted server-side session data.
        $staycationBooking = session('booking', []);

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
                'processor' => fn() => redirect()->route('booking.staycationPayment')->with(
                    'error',
                    'Please complete payment using secure checkout.'
                )
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
        Log::info('Initiate Payment Request', [
            'has_promo' => $request->filled('applied_promo_code') || $request->filled('applied_promo_id'),
            'payment_method' => $request->input('payment_method'),
            'booking_type' => $booking['booking_type'] ?? null,
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'nullable|string|in:paymongo,qr,gcash,paymaya,bank,qrph,grab_pay,billease',
            'applied_promo_id' => 'nullable|exists:promo_codes,id',
            'applied_promo_code' => 'nullable|string',
            'terms_and_policy_accepted' => 'sometimes|accepted',
            'terms_accepted' => 'sometimes|accepted',
            'camp_waiver_accepted' => 'sometimes|accepted',
        ]);

        if (!$request->boolean('terms_and_policy_accepted') && !$request->boolean('terms_accepted')) {
            return redirect()->back()
                ->withErrors(['terms_and_policy_accepted' => 'You must accept the booking terms and policy.'])
                ->withInput();
        }

        $requiresCampWaiver = !empty($booking) && (($booking['booking_type'] ?? null) === 'staycation');
        if ($requiresCampWaiver && !$request->boolean('camp_waiver_accepted')) {
            return redirect()->back()
                ->withErrors(['camp_waiver_accepted' => 'You must accept the camp waiver before continuing.'])
                ->withInput();
        }

        if ($requiresCampWaiver) {
            $request->validate(array_merge($this->campWaiverRules(), [
                'camp_waiver_accepted' => 'accepted',
            ]));
        }

        if (!empty($booking) && (($booking['booking_type'] ?? null) === 'staycation')) {
            $booking = $this->normalizeAndRecalculateStaycationBooking($booking);
            session(['booking' => $booking]);
        }

        $acceptances = $requiresCampWaiver
            ? $this->buildStaycationAcceptanceData($request)
            : [];
        if ($requiresCampWaiver) {
            $booking['acceptances'] = $acceptances;
            session(['booking' => $booking]);
        }

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

        // Apply promo code discount only after server-side validation.
        $promoDiscount = 0.0;
        $appliedPromo = null;
        $promoValidation = $this->getValidatedPromoDiscount(
            $request->filled('applied_promo_id') ? (int) $request->applied_promo_id : null,
            $request->input('applied_promo_code'),
            $grandTotal
        );
        $promoDiscount = (float) ($promoValidation['discount'] ?? 0);
        $appliedPromo = $promoValidation['promo'] ?? null;
        $grandTotal = max(0, $grandTotal - $promoDiscount);

        if ($appliedPromo) {
            Log::info('Promo code applied (server-side):', [
                'code' => $appliedPromo->code,
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

        // Send detailed line items so customer sees breakdown on PayMongo checkout
        // (promo discount is already subtracted from grandTotal; we don't send negative line items)

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
                'acceptances' => $acceptances,
                'payment_method' => $request->payment_method,
                'promo_code' => $appliedPromo?->code,
                'promo_discount' => $promoDiscount,
                'promo_id' => $appliedPromo?->id,
            ]
        ]);

        $successUrl = url('/booking/payment/success?checkout_id={CHECKOUT_ID}');
        $failedUrl = url('/booking/payment/failed?checkout_id={CHECKOUT_ID}');

        // Generate a reference id for this checkout so it appears on PayMongo checkout
        $referenceId = 'TXN-' . strtoupper(uniqid());
        // Persist reference in session payment data
        session()->put('staycation_payment_data.reference_id', $referenceId);

        // Create pending booking record in DB before redirecting to PayMongo
        // This ensures webhook can find the booking even if cache/session is lost
        $paymentDataForPending = array_merge(session('staycation_payment_data', []), [
            'reference_id' => $referenceId,
            'expected_amount_centavos' => (int) round($grandTotalWithVat * 100),
        ]);
        $pendingBooking = $this->paymentService->createPendingBooking($booking, $paymentDataForPending, 'staycation');

        // Persist checkout context so webhook can finalize booking even without success-page redirect.
        Cache::put('paymongo_checkout_context:' . $referenceId, [
            'type' => 'staycation',
            'booking_data' => $booking,
            'payment_data' => $paymentDataForPending,
            'created_at' => now()->toDateTimeString(),
        ], now()->addHours(24));

        Log::info('Creating PayMongo checkout', [
            'amount' => $grandTotalWithVat * 100,
            'success_url' => $successUrl,
            'failed_url' => $failedUrl,
            'line_items_count' => is_array($lineItems) ? count($lineItems) : 0,
            'reference_id' => $referenceId,
            'customer_email' => $request->email,
            'pending_booking_id' => $pendingBooking->id,
        ]);

        try {
            $expectedAmountCentavos = (int) round($grandTotalWithVat * 100);
            $firstAccommodation = $booking['accommodations'][0] ?? [];
            $roomName = strtolower((string) ($firstAccommodation['staycation']['name'] ?? $firstAccommodation['name'] ?? ''));
            $staycationCategory = str_contains($roomName, 'byot') || str_contains($roomName, 'car camping') || str_contains($roomName, 'bring your own')
                ? 'camping'
                : 'glamping';
            $checkoutMetadata = array_filter([
                'booking_type' => 'staycation',
                'staycation_category' => $staycationCategory,
                'checkin_date' => $booking['checkin'] ?? null,
                'checkout_date' => $booking['checkout'] ?? null,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ], fn ($value) => $value !== null && $value !== '');
            $checkout = $this->paymentService->createCheckout($expectedAmountCentavos, 'staycation', $successUrl, $failedUrl, $lineItems, $referenceId, $request->email, $checkoutMetadata);

            session()->put('staycation_payment_data.expected_amount_centavos', $expectedAmountCentavos);
            session()->put('staycation_payment_data.checkout_session_id', $checkout->id);
            Cache::put('paymongo_checkout_reference:' . $checkout->id, $referenceId, now()->addHours(24));
            Cache::put('paymongo_checkout_context:' . $referenceId, [
                'type' => 'staycation',
                'booking_data' => $booking,
                'payment_data' => array_merge(session('staycation_payment_data', []), [
                    'reference_id' => $referenceId,
                    'checkout_session_id' => $checkout->id,
                    'expected_amount_centavos' => $expectedAmountCentavos,
                ]),
                'created_at' => now()->toDateTimeString(),
            ], now()->addHours(24));

            // Update pending booking with checkout session ID
            $pendingBooking->update(['paymongo_checkout_id' => $checkout->id]);

            Log::info('PayMongo checkout created successfully', ['checkout_url' => $checkout->checkout_url]);
            return redirect($checkout->checkout_url);
        } catch (\Exception $e) {
            Log::error('PayMongo checkout creation failed: ' . $e->getMessage());
            // Mark pending booking as failed
            $pendingBooking->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
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
            $lockKey = 'staycation_payment_processed_' . $checkoutId;
            if (session()->has($lockKey)) {
                return redirect()->route('booking.confirmation');
            }

            $bookingData = session('staycation_booking_data');
            $paymentData = session('staycation_payment_data');

            if (!$bookingData || !$paymentData) {
                return redirect()->route('booking.payment')->with('error', 'Booking data not found.');
            }

            $expectedCheckoutId = $paymentData['checkout_session_id'] ?? null;
            if (!empty($expectedCheckoutId) && $expectedCheckoutId !== $checkoutId) {
                Log::warning('Checkout ID mismatch during payment success', [
                    'expected' => $expectedCheckoutId,
                    'actual' => $checkoutId,
                ]);
                return redirect()->route('booking.payment')->with('error', 'Checkout session mismatch.');
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $checkoutAttributes = $data['data']['attributes'] ?? [];
            $paymentStatus = $checkoutAttributes['payment_status'] ?? null;
            $paymentIntentStatus = $checkoutAttributes['payment_intent']['status'] ?? null;
            $isPaid = $paymentStatus === 'paid' || $paymentIntentStatus === 'succeeded';

            $expectedAmountCentavos = (int) ($paymentData['expected_amount_centavos'] ?? 0);
            $paidAmountCentavos = null;
            $firstPayment = $checkoutAttributes['payments'][0]['attributes'] ?? null;
            if (is_array($firstPayment) && isset($firstPayment['amount'])) {
                $paidAmountCentavos = (int) $firstPayment['amount'];
            } elseif (isset($checkoutAttributes['amount'])) {
                $paidAmountCentavos = (int) $checkoutAttributes['amount'];
            }

            $referenceCandidates = [
                $checkoutAttributes['reference'] ?? null,
                $checkoutAttributes['reference_number'] ?? null,
                $checkoutAttributes['metadata']['reference'] ?? null,
                $checkoutAttributes['metadata']['reference_id'] ?? null,
            ];
            $actualReference = collect($referenceCandidates)->first(fn ($value) => !empty($value));
            $expectedReference = $paymentData['reference_id'] ?? null;

            if ($expectedAmountCentavos > 0 && $paidAmountCentavos !== null && $paidAmountCentavos !== $expectedAmountCentavos) {
                Log::warning('PayMongo paid amount mismatch', [
                    'checkout_id' => $checkoutId,
                    'expected' => $expectedAmountCentavos,
                    'actual' => $paidAmountCentavos,
                ]);
                return redirect()->route('booking.payment')->with('error', 'Payment amount mismatch.');
            }

            if (!empty($expectedReference) && !empty($actualReference) && $expectedReference !== $actualReference) {
                Log::warning('PayMongo reference mismatch', [
                    'checkout_id' => $checkoutId,
                    'expected' => $expectedReference,
                    'actual' => $actualReference,
                ]);
                return redirect()->route('booking.payment')->with('error', 'Payment reference mismatch.');
            }

            if ($isPaid) {
                $referenceId = $paymentData['reference_id'] ?? null;

                // Find the pending booking by transaction_id (which is the referenceId)
                $existingBooking = Booking::where('transaction_id', $referenceId)
                    ->where('payment_status', 'pending')
                    ->first();

                if ($existingBooking) {
                    // Update the pending booking to confirmed
                    $paymentData['checkout_id'] = $checkoutId;
                    $paymentData['expected_amount_centavos'] = $expectedAmountCentavos;
                    if ($paidAmountCentavos !== null) {
                        $paymentData['paid_amount_centavos'] = $paidAmountCentavos;
                    }
                    $this->paymentService->confirmPendingBooking($existingBooking, $paymentData);

                    $paymentReference = $checkoutAttributes['payments'][0]['id'] ?? $checkoutId;
                    $existingBooking->update([
                        'payment_reference' => $paymentReference,
                    ]);

                    $this->syncConfirmedStaycationSession(
                        $existingBooking->fresh(),
                        $paymentData,
                        $checkoutId
                    );

                    session()->put($lockKey, true);

                    $this->applySuccessfulPromoUsage(
                        !empty($paymentData['promo_id']) ? PromoCode::find($paymentData['promo_id']) : null,
                        $existingBooking->fresh(),
                        $paymentData
                    );

                    // Clear session
                    session()->forget(['staycation_booking_data', 'staycation_payment_data']);

                    // Clear cache
                    if (!empty($referenceId)) {
                        Cache::forget('paymongo_checkout_context:' . $referenceId);
                    }
                    Cache::forget('paymongo_checkout_reference:' . $checkoutId);

                    return redirect()->route('booking.confirmation')->with('success', 'Payment processed successfully!');
                }

                // Prevent duplicate booking creation on refresh/revisit.
                if (session()->has($lockKey)) {
                    return redirect()->route('booking.confirmation');
                }

                $paymentData['checkout_id'] = $checkoutId;
                $paymentData['expected_amount_centavos'] = $expectedAmountCentavos;
                if ($paidAmountCentavos !== null) {
                    $paymentData['paid_amount_centavos'] = $paidAmountCentavos;
                }
                $result = $this->paymentService->processStaycationPayment($bookingData, $paymentData);
                session()->put($lockKey, true);

                $referenceId = $paymentData['reference_id'] ?? null;
                if (!empty($referenceId)) {
                    Cache::forget('paymongo_checkout_context:' . $referenceId);
                }
                Cache::forget('paymongo_checkout_reference:' . $checkoutId);

                $this->applySuccessfulPromoUsage(
                    !empty($paymentData['promo_id']) ? PromoCode::find($paymentData['promo_id']) : null,
                    Booking::where('transaction_id', $referenceId)->first(),
                    $paymentData
                );

                // Clear session
                session()->forget(['staycation_booking_data', 'staycation_payment_data']);

                return $result;
            }

            return redirect()->route('booking.payment')->with('error', 'Payment was not successful.');
        } catch (\Exception $e) {
            Log::error('PayMongo checkout verification failed: ' . $e->getMessage());
            return redirect()->route('booking.payment')->with('error', 'Payment verification failed.');
        }
    }

    private function syncConfirmedStaycationSession(Booking $booking, array $paymentData, string $checkoutId): void
    {
        if (!$this->canUseSession()) {
            return;
        }

        $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];
        $customerInfo = is_array($bookingData['customer_info'] ?? null) ? $bookingData['customer_info'] : [];
        $resolvedCustomerInfo = array_merge($customerInfo, [
            'first_name' => $paymentData['first_name'] ?? ($customerInfo['first_name'] ?? ''),
            'last_name' => $paymentData['last_name'] ?? ($customerInfo['last_name'] ?? ''),
            'name' => trim(
                ($paymentData['first_name'] ?? ($customerInfo['first_name'] ?? '')) . ' ' .
                ($paymentData['last_name'] ?? ($customerInfo['last_name'] ?? ''))
            ),
            'email' => $paymentData['email'] ?? ($customerInfo['email'] ?? ''),
            'phone' => $paymentData['phone'] ?? ($customerInfo['phone'] ?? ''),
        ]);

        $bookingData['transaction_id'] = $booking->transaction_id;
        $bookingData['payment_method'] = $booking->payment_method ?? ($paymentData['payment_method'] ?? 'paymongo');
        $bookingData['payment_date'] = optional($booking->updated_at)->toDateTimeString() ?? now()->toDateTimeString();
        $bookingData['paymongo_checkout_id'] = $checkoutId;
        $bookingData['payment_reference'] = $booking->payment_reference ?? ($paymentData['payment_reference'] ?? null);
        $bookingData['customer_info'] = $resolvedCustomerInfo;

        $paymentSummary = is_array($bookingData['payment_summary'] ?? null) ? $bookingData['payment_summary'] : [];
        $paymentSummary['checkout_id'] = $checkoutId;
        $paymentSummary['grand_total_paid'] = (float) $booking->total_amount;
        $bookingData['payment_summary'] = $paymentSummary;

        $finalTotals = is_array($bookingData['final_totals'] ?? null) ? $bookingData['final_totals'] : [];
        $finalTotals['grand_total'] = (float) $booking->total_amount;
        $bookingData['final_totals'] = $finalTotals;

        session([
            'booking' => $bookingData,
            'staycation_customer_info' => $resolvedCustomerInfo,
            'staycation_selected_foods' => $bookingData['selected_foods'] ?? [],
        ]);
    }

    private function canUseSession(): bool
    {
        try {
            return request()->hasSession();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function recordPromoUsage(PromoCode $promo, ?Booking $booking, array $paymentData): void
    {
        $transactionId = $booking?->transaction_id ?? ($paymentData['reference_id'] ?? null);

        if (empty($transactionId)) {
            return;
        }

        PromoCodeUsage::updateOrCreate(
            [
                'promo_code_id' => $promo->id,
                'booking_transaction_id' => $transactionId,
            ],
            [
                'booking_id' => $booking?->id,
                'checkout_id' => $paymentData['checkout_id'] ?? $paymentData['checkout_session_id'] ?? null,
                'payment_reference' => $paymentData['payment_reference'] ?? null,
                'discount_amount' => (float) ($paymentData['promo_discount'] ?? 0),
            ]
        );
    }

    private function applySuccessfulPromoUsage(?PromoCode $promo, ?Booking $booking, array $paymentData): void
    {
        if (!$promo) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($promo, $booking, $paymentData) {
            $lockedPromo = PromoCode::whereKey($promo->id)->lockForUpdate()->first();

            if (!$lockedPromo) {
                return;
            }

            $verifiedUsageCount = PromoCodeUsage::where('promo_code_id', $lockedPromo->id)->count();

            if ($lockedPromo->usage_limit && $verifiedUsageCount >= $lockedPromo->usage_limit) {
                $lockedPromo->forceFill([
                    'usage_count' => $verifiedUsageCount,
                ])->save();

                return;
            }

            $this->recordPromoUsage($lockedPromo, $booking, $paymentData);

            $verifiedUsageCount = PromoCodeUsage::where('promo_code_id', $lockedPromo->id)->count();

            $lockedPromo->forceFill([
                'usage_count' => $verifiedUsageCount,
            ])->save();
        });

        PromoCode::syncAutomaticStatus();
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
            $paymentIntentStatus = data_get($data, 'data.attributes.payment_intent.status', 'unknown');
            $lastPaymentError = data_get($data, 'data.attributes.payment_intent.last_payment_error');

            if ($paymentIntentStatus === 'succeeded') {
                Log::info('PayMongo failure callback resolved to successful staycation payment; redirecting to success handler', [
                    'checkout_id' => $checkoutId,
                ]);

                return redirect()->route('booking.payment.success', ['checkout_id' => $checkoutId]);
            }

            // Log the failure reason
            Log::warning('PayMongo payment failed', [
                'checkout_id' => $checkoutId,
                'status' => $paymentIntentStatus,
                'error' => $lastPaymentError,
            ]);

            $this->paymentService->failPendingBookingForCheckout($checkoutId, null, 'failed', 'paymongo_failed_callback');

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

                if (empty($booking['checkin']) || empty($booking['checkout'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select your check-in and check-out dates first.',
                    ], 422);
                }

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
                    if (!$staycation) {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid room selection for room key {$originalKey}. Please reselect your room.",
                        ], 422);
                    }
                    
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
                    
                    $isSpecialRoom = $this->isSpecialStaycationRoom($staycation);

                    // Use CENTRALIZED pricing method for consistency across all booking steps
                    // IMPORTANT: pass real adults/children so pricing + pets are computed correctly
                    $adults = (int)($room['adults'] ?? 1);
                    $children = (int)($room['children'] ?? 0);
                    [$adults, $children] = $this->clampRoomGuestCounts($staycation, $adults, $children);
                    $totalGuests = $adults + $children;
                    $maxGuestCapacity = $this->resolveMaxGuestCapacity($staycation);
                    if ($totalGuests > $maxGuestCapacity) {
                        return response()->json([
                            'success' => false,
                            'message' => "Guest limit exceeded for {$staycation->name}. Maximum {$maxGuestCapacity} guests allowed.",
                        ], 422);
                    }

                    $extraMattresses = $this->resolveExtraMattressCount(
                        $staycation,
                        $adults,
                        $children,
                        (int)($room['extra_mattress'] ?? 0)
                    );

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
                        'children'       => $children,
                        'extra_mattress' => $extraMattresses,
                        'quantity'       => $isSpecialRoom ? max(1, (int)($room['extra_mattress'] ?? 1)) : ($adults + (int)($room['children'] ?? 0)),
                        'pets'           => ($room['pets'] ?? 'no'),
                        'pet_agreement'  => (bool)($room['pet_agreement'] ?? false),
                        'num_pets'       => $numPets,
                        'base_price'     => $accommodationPrice,
                        'mattress_price' => $mattressPrice,
                        'additional_adults' => $pricingResult['additional_adults'] ?? $extraMattresses,
                        'additional_adult_price' => $pricingResult['additional_adult_price'] ?? $mattressPrice,
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
                $normalizedFoods = $this->normalizeStaycationFoods($data['selected_foods']);
                if (empty($normalizedFoods)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid food selection data.',
                    ], 422);
                }

                $booking['selected_foods'] = $normalizedFoods;
                $booking = $this->normalizeAndRecalculateStaycationBooking($booking);
                $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [2]));
                $booking['current_step'] = 3;

                session(['booking' => $booking]);

                return response()->json([
                    'success' => true,
                ]);
            }


            $validated = $request->validate(ValidationService::staycationPostRules());
            $booking = session('booking', []);

            // Capacity overflow is allowed; extra mattress is auto-computed from guest overflow.

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
            'checkout' => 'required|date_format:Y-m-d|after:checkin',
            'rooms_count' => 'required|integer|min:1|max:4',
            'adults_total' => 'required|integer|min:1',
            'children_total' => 'nullable|integer|min:0',
            'pets_total' => 'required|integer|min:0',
            'rooms' => 'required|array',
            'rooms.*.adults' => 'required|integer|min:1',
            'rooms.*.children' => 'nullable|integer|min:0',
            'rooms.*.pets' => 'integer|min:0',
        ]);

        // Store booking data in session
        session(['booking' => [
            'checkin' => $validated['checkin'],
            'checkout' => $validated['checkout'],
            'rooms_count' => $validated['rooms_count'],
            'adults_total' => $validated['adults_total'],
            'children_total' => (int)($validated['children_total'] ?? 0),
            'pets_total' => $validated['pets_total'],
            'rooms' => $validated['rooms'],
        ]]);

        return response()->json(['success' => true, 'message' => 'Booking data saved to session']);
    }

    public function staycationAvailability(Request $request)
    {
        $sortBy = $request->string('sort_by', 'featured')->toString();
        $sortOptions = [
            'featured' => 'Featured',
            'name_asc' => 'Alphabetically, A-Z',
            'name_desc' => 'Alphabetically, Z-A',
            'price_asc' => 'Price, low to high',
            'price_desc' => 'Price, high to low',
            'oldest' => 'Date, old to new',
            'newest' => 'Date, new to old',
        ];
        if (!array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'featured';
        }

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

        // Use the check-in date season for the headline/base posted rate.
        // Per-night pricing still resolves each date independently below.
        $checkinCarbon = \Carbon\Carbon::parse($checkin);
        $checkoutCarbon = \Carbon\Carbon::parse($checkout);
        $currentSeason = $this->getSeasonTypeForDate($checkinCarbon);

        Log::info('Season for staycationAvailability check-in date: ' . $currentSeason);

        // Get all staycations from admin panel
        $staycationsQuery = Staycation::with([
            'texts',
            'swiperImages'
        ]);

        switch ($sortBy) {
            case 'name_asc':
                $staycationsQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $staycationsQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $staycationsQuery->orderByRaw('COALESCE(price, 0) asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $staycationsQuery->orderByRaw('COALESCE(price, 0) desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $staycationsQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $staycationsQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $staycationsQuery->ordered();
                break;
        }

        $staycations = $staycationsQuery->get();

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
            $image = $staycation->image ?? \App\Helpers\ImageHelper::placeholder();

            $name = strtolower((string) $staycation->name);
            $storedType = strtolower((string) ($staycation->type ?? ''));
            $isDomeOrBell = strpos($name, 'dome') !== false || strpos($name, 'bell') !== false;
            if ($isDomeOrBell) {
                $type = 'glamping';
            } elseif (in_array($storedType, ['glamping', 'camping', 'byot', 'car camping'], true)) {
                $type = $storedType;
            } elseif (strpos($name, 'byot') !== false || strpos($name, 'bring your own') !== false || strpos($name, 'car camping') !== false) {
                $type = 'camping';
            } elseif (strpos($name, 'glamping') !== false || strpos($name, 'dome') !== false || strpos($name, 'bell') !== false) {
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
                'adults_limit' => (int)($staycation->adults ?? 0),
                'children_limit' => (int)($staycation->children ?? 0),
                'included_guest_capacity' => $this->resolveIncludedGuestCapacity($staycation),
                'max_guest_capacity' => $this->resolveMaxGuestCapacity($staycation),
                'capacity' => (int)($staycation->capacity ?? 0),
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

        return view('booking.staycation-availability', compact('staycations', 'availability', 'roomDetails', 'checkin', 'checkout', 'currentSeason', 'roomsCount', 'adultsTotal', 'childrenTotal', 'rooms', 'maxPetsPerRoom', 'vatRate', 'vatRatePercentage', 'sortBy', 'sortOptions'));
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
        ->orderBy(
            PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'),
            'desc'
        )
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
            $weekendDays = ['Fri', 'Sat'];
            $weekdayDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
            
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
        $sortBy = request()->query('sort_by', 'featured');
        $sortOptions = [
            'featured' => 'Featured',
            'name_asc' => 'Alphabetically, A-Z',
            'name_desc' => 'Alphabetically, Z-A',
            'price_asc' => 'Price, low to high',
            'price_desc' => 'Price, high to low',
            'oldest' => 'Date, old to new',
            'newest' => 'Date, new to old',
        ];
        if (!array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'featured';
        }
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
        $foodMenusQuery = Food::with(['swiperImages', 'texts'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('category', ['messhall', 'cook_your_own'])
                      ->orWhere('is_special_option', true);
            });

        switch ($sortBy) {
            case 'name_asc':
                $foodMenusQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $foodMenusQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $foodMenusQuery->orderBy('price', 'asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $foodMenusQuery->orderBy('price', 'desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $foodMenusQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $foodMenusQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $foodMenusQuery->orderBy('category');
                if (Schema::hasColumn('foods', 'meal_type') && Schema::hasColumn('foods', 'sort_order')) {
                    $foodMenusQuery
                        ->orderByRaw("CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")
                        ->orderBy('sort_order');
                }
                $foodMenusQuery->orderBy('name');
                break;
        }
        $foodMenus = $foodMenusQuery->get();

        // Fetch food packages for staycation
        $foodPackagesQuery = FoodPackage::with(['foods' => function($q) {
            $q->select('foods.id', 'foods.name', 'foods.price', 'foods.category');
        }])
            ->where('is_active', true)
            ;

        switch ($sortBy) {
            case 'name_asc':
                $foodPackagesQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $foodPackagesQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $foodPackagesQuery->orderByRaw('COALESCE(price, 0) asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $foodPackagesQuery->orderByRaw('COALESCE(price, 0) desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $foodPackagesQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $foodPackagesQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $foodPackagesQuery->orderBy('category');
                if (Schema::hasColumn('food_packages', 'sort_order')) {
                    $foodPackagesQuery->orderBy('sort_order');
                }
                $foodPackagesQuery->orderBy('name');
                break;
        }
        $foodPackages = $foodPackagesQuery->get();

        // Group food menus by meal_type for display
        $foodMenusByMealType = $foodMenus->groupBy('meal_type');

        // Ensure consistent ordering: breakfast, lunch, dinner, then others
        $mealTypeOrder = ['breakfast' => 1, 'lunch' => 2, 'dinner' => 3];
        $foodMenusByMealType = $foodMenusByMealType->sortBy(function($items, $mealType) use ($mealTypeOrder) {
            return $mealTypeOrder[$mealType] ?? 4;
        });

        // Fetch food packages section settings from admin
        $packagesSettings = [
            'show' => (bool)FoodPackageSetting::getValue('show', 1),
            'title' => FoodPackageSetting::getValue('title') ?? 'Meal Packages (Special Bundles)',
            'description' => FoodPackageSetting::getValue('description') ?? 'Curated meal bundles with special pricing',
            'icon' => FoodPackageSetting::getValue('icon') ?? 'bi-bag-heart'
        ];

        // Fetch staycation data for display in summary (array keys are room IDs)
        $accommodationIds = array_keys($booking['accommodations'] ?? []);
        $staycations = Staycation::ordered()->whereIn('id', $accommodationIds)->get();
        $packageCategoryLabels = FoodPackageSetting::getPackageCategoryLabels();
        $foodBookingSectionCopy = FoodPackageSetting::getFoodBookingSectionCopy();

        // Get dynamic VAT rate
        $vatService = app(\App\Services\VatService::class);
        $vatRate = $vatService->getCurrentVatRate();
        $vatRatePercentage = $vatService->getCurrentVatRatePercentage();

        return view('booking.staycation-food', compact('booking', 'foodMenus', 'foodMenusByMealType', 'foodPackages', 'staycations', 'packagesSettings', 'packageCategoryLabels', 'foodBookingSectionCopy', 'vatRate', 'vatRatePercentage', 'sortBy', 'sortOptions'));
    }

    public function saveFoodSelection(Request $request)
    {
        try {
            // Simple validation - just ensure selected_foods is an array
            $validated = $request->validate([
                'selected_foods' => 'required|array|min:1',
            ]);

            // Normalize against DB prices/names/categories to avoid payload tampering
            $selectedFoods = $this->normalizeStaycationFoods($request->input('selected_foods', []));
            if (empty($selectedFoods)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid food selection.'
                ], 422);
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
            $booking = $this->normalizeAndRecalculateStaycationBooking($booking);
            
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

        $staycations = $query->ordered()->get();

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
        $staycations = Staycation::ordered()->get();

        $prices = [];
        foreach ($staycations as $staycation) {
            $checkinCarbon = \Carbon\Carbon::parse($checkin);
            $season = $this->getSeasonTypeForDate($checkinCarbon);
            $seasonKey = $this->mapSeasonToKey($season);

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
        $seasonType = $this->determineSeasonTypeForDate($date);

        // Map season types to rate keys
        $rateKeyMap = [
            'non-peak' => 'nonPeak',
            'peak' => 'peak',
            'super-peak' => 'superPeak'
        ];

        $rateKey = $rateKeyMap[$seasonType] ?? 'nonPeak';
        $configuredRate = $this->pricingService->getStaycationRate($staycation, \Carbon\Carbon::parse($date), $rateKey);

        if ($configuredRate > 0) {
            return $configuredRate;
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
     * Determine if a staycation room is BYOT/Car Camping (special-room pricing).
     */
    protected function isSpecialStaycationRoom(Staycation $staycation): bool
    {
        $roomNameLower = strtolower($staycation->name ?? '');
        $isBYOT = str_contains($roomNameLower, 'byot')
            || str_contains($roomNameLower, 'bring your own tent')
            || str_contains($roomNameLower, 'bring your own');
        $isCarCamping = !$isBYOT && str_contains($roomNameLower, 'car camping');

        return $isBYOT || $isCarCamping;
    }

    /**
     * Extra mattress should only apply to glamping accommodations.
     */
    protected function supportsExtraMattress(Staycation $staycation): bool
    {
        if ($this->isSpecialStaycationRoom($staycation)) {
            return false;
        }

        $type = strtolower((string)($staycation->type ?? ''));
        if ($type === 'glamping') {
            return true;
        }

        // Fallback for legacy/misaligned data where type may be stale but room name is glamping.
        $roomName = strtolower((string)($staycation->name ?? ''));
        return str_contains($roomName, 'glamping')
            || str_contains($roomName, 'dome')
            || str_contains($roomName, 'bell');
    }

    /**
     * Resolve extra mattress count.
     * For regular rooms: use submitted value but clamp to allowed overflow limit.
     * For BYOT/Car Camping: keep submitted quantity (minimum 1).
     */
    protected function resolveExtraMattressCount(
        Staycation $staycation,
        int $adults,
        int $children,
        int $submittedExtraMattress = 0
    ): int {
        if ($this->isSpecialStaycationRoom($staycation)) {
            return max(1, $submittedExtraMattress);
        }

        if (!$this->supportsExtraMattress($staycation)) {
            return 0;
        }

        // Legacy key name, but now this is auto-computed additional-adult count.
        return $this->resolveAdditionalAdultCount($staycation, $adults);
    }

    /**
     * Additional adult count is automatic and based on adult headcount only.
     * Rule:
     * - base included adults come from included_guest_capacity
     * - extra adults are capped by max_guest_capacity
     * - children do not count as additional adults
     */
    protected function resolveAdditionalAdultCount(Staycation $staycation, int $adults): int
    {
        if ($this->isSpecialStaycationRoom($staycation) || !$this->supportsExtraMattress($staycation)) {
            return 0;
        }

        $baseIncludedAdults = $this->resolveIncludedGuestCapacity($staycation);
        $maxAdditionalAdults = max(0, $this->resolveMaxGuestCapacity($staycation) - $baseIncludedAdults);
        $adultCount = max(1, $adults);
        $adultOverflow = max(0, $adultCount - $baseIncludedAdults);

        return min($maxAdditionalAdults, $adultOverflow);
    }

    /**
     * Resolve the included guest capacity used to compute overflow/extra mattress.
     * Priority:
     * 1) explicit included_guest_capacity
     * 2) adults limit (represents base beds/included guests)
     * 3) capacity (legacy fallback)
     * 4) minimum fallback of 1
     */
    protected function resolveIncludedGuestCapacity(Staycation $staycation): int
    {
        $explicitIncluded = max(0, (int)($staycation->included_guest_capacity ?? 0));
        if ($explicitIncluded > 0) {
            return $explicitIncluded;
        }

        $adultsLimit = max(0, (int)($staycation->adults ?? 0));
        if ($adultsLimit > 0) {
            return $adultsLimit;
        }

        // Legacy fallback when base included is not explicitly configured.
        $baseCapacity = max(0, (int)($staycation->capacity ?? 0));
        if ($baseCapacity > 0) {
            return $baseCapacity;
        }
        
        return 1;
    }

    /**
     * Resolve the maximum guest capacity allowed per room.
     * Falls back to included capacity if no explicit max is configured.
     */
    protected function resolveMaxGuestCapacity(Staycation $staycation): int
    {
        $included = $this->resolveIncludedGuestCapacity($staycation);
        $configuredMax = max(0, (int)($staycation->max_guest_capacity ?? 0));

        if ($configuredMax > 0) {
            return max($included, $configuredMax);
        }

        // Legacy fallback: derive from adults + children limits when present.
        $adultsLimit = max(0, (int)($staycation->adults ?? 0));
        $childrenLimit = max(0, (int)($staycation->children ?? 0));
        $derivedMax = $adultsLimit + $childrenLimit;
        if ($derivedMax > 0) {
            return max($included, $derivedMax);
        }

        // Last fallback: use capacity if bigger than included.
        $legacyCapacity = max(0, (int)($staycation->capacity ?? 0));
        if ($legacyCapacity > 0) {
            return max($included, $legacyCapacity);
        }

        // Backward-compatible fallback for glamping rooms without explicit max capacity:
        // allow up to 2 additional adults beyond included capacity.
        if ($this->supportsExtraMattress($staycation)) {
            return max($included, $included + 2);
        }

        return max(1, $included);
    }

    /**
     * Clamp room guest counts to the configured maximum capacity.
     * Preference: reduce children first, then adults (keeping at least 1 adult).
     */
    protected function clampRoomGuestCounts(Staycation $staycation, int $adults, int $children): array
    {
        $adults = max(1, $adults);
        $children = max(0, $children);

        if ($this->isSpecialStaycationRoom($staycation)) {
            return [$adults, $children];
        }

        $maxGuests = $this->resolveMaxGuestCapacity($staycation);
        $total = $adults + $children;
        if ($total <= $maxGuests) {
            return [$adults, $children];
        }

        $overflow = $total - $maxGuests;
        if ($children >= $overflow) {
            $children -= $overflow;
            return [$adults, $children];
        }

        $overflow -= $children;
        $children = 0;
        $adults = max(1, $adults - $overflow);

        if (($adults + $children) > $maxGuests) {
            $adults = max(1, $maxGuests - $children);
        }

        return [$adults, $children];
    }

    /**
     * Calculate mattress fee for a given quantity and number of nights
     * Centralized method to avoid repeating the calculation
     */
    protected function calculateMattressFee(int $mattressCount, int $nights)
    {
        return $this->calculateAdditionalAdultFee($mattressCount, $nights);
    }

    /**
     * Calculate additional adult fee for a given quantity and number of nights.
     */
    protected function calculateAdditionalAdultFee(int $additionalAdultCount, int $nights)
    {
        $pricePerNight = BookingSetting::getValue('mattress_price_per_night', 1200);
        return (int)$additionalAdultCount * (int)$pricePerNight * max(1, $nights);
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
