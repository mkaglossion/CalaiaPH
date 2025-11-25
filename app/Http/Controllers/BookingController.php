<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmation;
use DateTime;

class BookingController extends Controller
{
    public function debugSession()
    {
        // Dump the entire session
        dd(session()->all());
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'day-visit');
        $allowedTypes = ['day-visit', 'room', 'overnight', 'daytour'];
        // Map 'daytour' to 'day-visit' for consistency
        if ($type === 'daytour') {
            $type = 'day-visit';
        }
        if (!in_array($type, $allowedTypes)) {
            $type = 'day-visit';
        }

        $package = $request->get('package', 'Day Visit Package');
        $price = $request->get('price', '1000');

        // Clean the price to remove currency symbols and get numeric value
        $price = preg_replace('/[^\d.]/', '', $price);

        // Set max guests based on package
        $max_guests = 50; // default
        $default_guests = 1; // default
        if (str_contains($package, 'Family Fun Package')) {
            $max_guests = 6; // max 6 guests per family
            $default_guests = 6; // auto-set to 6 for family package
        }

        session(['max_guests' => $max_guests]);

        // Get capacities and inventory for calendar
        $settings = (object) [
            'day_visit_capacity' => 10,
            'room_capacity' => 5,
            'overnight_capacity' => 3,
        ];

        $defaultInventory = [
            'day-visit' => [
                '2025-11-20' => 8,
                '2025-11-21' => 5,
                '2025-11-22' => 10,
            ],
            'room' => [
                '2025-11-20' => 3,
                '2025-11-21' => 2,
                '2025-11-22' => 5,
            ],
            'overnight' => [
                '2025-11-20' => 2,
                '2025-11-21' => 1,
                '2025-11-22' => 3,
            ],
        ];

        $inventory = session('inventory', $defaultInventory);

        return view('booking.reservation', compact('type', 'package', 'price', 'settings', 'inventory', 'max_guests', 'default_guests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'required|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:' . session('max_guests', 50),
            'special_requests' => 'nullable|string|max:1000',
            'type' => 'required|string|in:day-visit,room,overnight',
            'package' => 'required|string',
            'price' => 'required|numeric',
        ]);

        // Get current inventory from session
        $defaultInventory = [
            'day-visit' => ['2025-11-20' => 8, '2025-11-21' => 5, '2025-11-22' => 10],
            'room' => ['2025-11-20' => 3, '2025-11-21' => 2, '2025-11-22' => 5],
            'overnight' => ['2025-11-20' => 2, '2025-11-21' => 1, '2025-11-22' => 3],
        ];
        $inventory = session('inventory', $defaultInventory);

        // Get booking type and date
        $type = $request->input('type'); // 'room', 'day-visit', or 'overnight'
        $date = $validated['booking_date'];

        // Check availability - for demo, assume available if not in inventory
        if (isset($inventory[$type][$date]) && $inventory[$type][$date] <= 0) {
            return redirect()->back()->with('error', 'No availability for the selected date and type.');
        }

        // Deduct inventory for the booked date
        if (isset($inventory[$type][$date])) {
            $inventory[$type][$date] = max(0, $inventory[$type][$date] - 1);
            session(['inventory' => $inventory]);
        }

        // For day-visit, multiply price by number of guests (except for family packages which are already per family)
        if ($validated['type'] === 'day-visit' && !str_contains($validated['package'], 'Family Fun Package')) {
            $validated['price'] = $validated['price'] * $validated['guests'];
        }

        // Store booking data in session for activities
        session(['booking_data' => $validated]);

        // Redirect to activities page
        return redirect()->route('booking.activities');
    }

    public function activities()
    {
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        $activities = $this->getActivitiesData();

        return view('booking.activities', compact('bookingData', 'activities'));
    }

    public function getActivitiesApi()
    {
        $activities = $this->getActivitiesData();
        return response()->json($activities);
    }

    public function storeActivities(Request $request)
    {
        $request->validate([
            'activities' => 'required|array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined',
        ]);

        $bookingData = session('booking_data');

        if (!$bookingData) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        // Store selected activities in session
        session(['selected_activities' => $request->activities]);

        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    public function staycationActivities()
    {
        $booking = session('booking', []);

        if (empty($booking['checkin']) || empty($booking['checkout']) || empty($booking['accommodations'])) {
            return redirect()->route('booking.staycation')->with('error', 'Please complete accommodation selection first.');
        }

        $activities = $this->getActivitiesData();

        return view('booking.staycation-activities', compact('booking', 'activities'));
    }

    public function storeStaycationActivities(Request $request)
    {
        $request->validate([
            'activities' => 'array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined,custom',
        ]);

        $booking = session('booking', []);

        if (empty($booking)) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        // Store selected activities in session
        $booking['selected_activities'] = $request->activities;

        // Calculate final totals (same calculation as JavaScript)
        $checkinDate = $booking['checkin'] ?? null;
        $checkoutDate = $booking['checkout'] ?? null;
        $datesSelected = !empty($checkinDate) && !empty($checkoutDate);

        $nights = 0;
        if ($datesSelected) {
            $start = new DateTime($checkinDate);
            $end = new DateTime($checkoutDate);
            $diff = $start->diff($end);
            $nights = $diff->days;
        }

        $totalGuests = collect($booking['accommodations'])->sum('guests');

        $accommodationTotal = 0;
        if ($datesSelected) {
            $start = new DateTime($checkinDate);
            $end = new DateTime($checkoutDate);

            foreach ($booking['accommodations'] ?? [] as $acc) {
                if (!empty($acc) && !empty($acc['staycation'])) {
                    $quantity = $acc['guests'] ?? 1;
                    $name = strtolower($acc['staycation']['name']);
                    $isBYOT = str_contains($name, 'byot');
                    $isCarCamping = str_contains($name, 'car camping');

                    if (str_contains($name, 'standard') || str_contains($name, 'premium')) {
                        $current = clone $start;
                        while ($current < $end) {
                            $day = (int)$current->format('w');
                            $isWeekend = $day === 5 || $day === 6;
                            $basePrice = 0;
                            if (str_contains($name, 'standard')) {
                                $basePrice = $isWeekend ? 6000 : 4800;
                            } elseif (str_contains($name, 'premium')) {
                                $basePrice = $isWeekend ? 7200 : 5760;
                            }
                            $extraGuests = max(0, $quantity - 4);
                            $accommodationTotal += $basePrice + ($extraGuests * 400);
                            $current->modify('+1 day');
                        }
                    } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                        $accommodationTotal += 3000 * $nights;
                    } elseif ($isBYOT || $isCarCamping) {
                        $accommodationTotal += 300 * $quantity * $nights;
                    }
                }
            }

            // Add pet fees
            foreach ($booking['accommodations'] ?? [] as $acc) {
                if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
                    $accommodationTotal += 2000 * $acc['num_pets'];
                }
            }
        }

        $activitiesTotal = collect($request->activities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
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
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        return view('booking.payment', compact('bookingData'));
    }

    public function confirmation()
    {
        // Check for staycation booking first
        $staycationBookingData = session('staycation_booking_data');
        $staycationSelectedActivities = session('staycation_selected_activities', []);
        $staycationGrandTotal = session('staycation_grand_total', 0);
        $staycationCustomerInfo = session('staycation_customer_info', []);

        if ($staycationBookingData) {
            // Handle staycation confirmation
            // Clear session data after showing confirmation
            session()->forget(['staycation_booking_data', 'staycation_selected_activities', 'staycation_grand_total', 'staycation_customer_info']);

            return view('booking.confirmation', [
                'bookingData' => $staycationBookingData,
                'selectedActivities' => $staycationSelectedActivities,
                'grandTotal' => $staycationGrandTotal,
                'customerInfo' => $staycationCustomerInfo,
                'isStaycation' => true
            ]);
        }

        // Check for day visit booking
        $dayvisitBookingData = session('dayvisit_booking_data');
        $dayvisitSelectedActivities = session('dayvisit_selected_activities', []);
        $dayvisitGrandTotal = session('dayvisit_grand_total', 0);
        $dayvisitCustomerInfo = session('dayvisit_customer_info', []);

        if ($dayvisitBookingData) {
            // Handle day visit confirmation
            // Clear session data after showing confirmation
            session()->forget(['dayvisit_booking_data', 'dayvisit_selected_activities', 'dayvisit_grand_total', 'dayvisit_customer_info']);

            return view('booking.confirmation', [
                'bookingData' => $dayvisitBookingData,
                'selectedActivities' => $dayvisitSelectedActivities,
                'grandTotal' => $dayvisitGrandTotal,
                'customerInfo' => $dayvisitCustomerInfo,
                'isDayvisit' => true
            ]);
        }

        // Handle regular booking
        $bookingData = session('booking_data');
        $selectedActivities = session('selected_activities', []);
        $grandTotal = ($bookingData['price'] ?? 0) + collect($selectedActivities)->sum(function($activity) {
            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
        });

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Clear session data after showing confirmation
        session()->forget(['booking_data', 'selected_activities']);

        return view('booking.confirmation', compact('bookingData', 'selectedActivities', 'grandTotal'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:gcash,paymaya,bank',
            'terms' => 'accepted',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $bookingData = session('booking_data');
        $staycationBooking = session('booking', []);
        $dayvisitBooking = session('dayvisit_booking_data');

        if (!$bookingData && empty($staycationBooking) && !$dayvisitBooking) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Determine booking type
        $isStaycation = !empty($staycationBooking) && isset($staycationBooking['booking_type']) && $staycationBooking['booking_type'] === 'staycation';
        $isDayvisit = !empty($dayvisitBooking);

        if ($isStaycation) {
            // Handle staycation booking
            $selectedActivities = $staycationBooking['selected_activities'] ?? [];

            // Add customer info and payment details to booking data for receipt
            $staycationBooking['first_name'] = $request->first_name;
            $staycationBooking['last_name'] = $request->last_name;
            $staycationBooking['email'] = $request->email;
            $staycationBooking['phone'] = $request->phone;
            $staycationBooking['special_requests'] = $request->special_requests;
            $staycationBooking['payment_method'] = $request->payment_method;
            $staycationBooking['payment_date'] = now();
            $staycationBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());

            try {
                // Send receipt email for staycation using form data
                $customerEmail = $request->email ?? 'guest@example.com';
                Log::info('Sending staycation confirmation email to: ' . $customerEmail);
                Mail::to($customerEmail)->send(new BookingConfirmation($staycationBooking, $selectedActivities));
                Log::info('Staycation confirmation email sent successfully to: ' . $customerEmail);
            } catch (\Exception $e) {
                // Log the error but continue to confirmation
                Log::error('Staycation receipt email failed: ' . $e->getMessage());
                Log::error('Staycation email error details: ', ['email' => $customerEmail, 'booking' => $staycationBooking]);
            }

            // Store booking data for confirmation page
            $grandTotal = ($staycationBooking['final_totals']['accommodation_total'] ?? 0) + ($staycationBooking['final_totals']['activities_total'] ?? 0);

            // Store confirmation data in session
            session([
                'staycation_booking_data' => $staycationBooking,
                'staycation_selected_activities' => $selectedActivities,
                'staycation_grand_total' => $grandTotal,
                'staycation_customer_info' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'special_requests' => $request->special_requests,
                ]
            ]);

            // Clear session
            session()->forget('booking');

            return redirect()->route('booking.confirmation')->with('success', 'Staycation payment successful! Booking confirmed. Check your email for details.');
        } elseif ($isDayvisit) {
            // Handle day visit booking
            $selectedActivities = session('dayvisit_selected_activities', []);

            // Add payment details to booking data for receipt
            $dayvisitBooking['payment_method'] = $request->payment_method;
            $dayvisitBooking['payment_date'] = now();
            $dayvisitBooking['transaction_id'] = 'TXN-' . strtoupper(uniqid());

            try {
                // Send receipt email for day visit
                Log::info('Sending day visit confirmation email to: ' . $dayvisitBooking['email']);
                Mail::to($dayvisitBooking['email'])->send(new BookingConfirmation($dayvisitBooking, $selectedActivities));
                Log::info('Day visit confirmation email sent successfully to: ' . $dayvisitBooking['email']);
            } catch (\Exception $e) {
                // Log the error but continue to confirmation
                Log::error('Day visit receipt email failed: ' . $e->getMessage());
                Log::error('Day visit email error details: ', ['email' => $dayvisitBooking['email'], 'booking' => $dayvisitBooking]);
            }

            // Calculate grand total
            $grandTotal = ($dayvisitBooking['price'] ?? 0) + collect($selectedActivities)->sum(function($activity) {
                return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
            });
        
            // Add grand total to booking data for email
            $dayvisitBooking['grand_total'] = $grandTotal;
        
            // Store confirmation data in session
            session([
                'dayvisit_booking_data' => $dayvisitBooking,
                'dayvisit_selected_activities' => $selectedActivities,
                'dayvisit_grand_total' => $grandTotal,
                'dayvisit_customer_info' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'special_requests' => $request->special_requests,
                ]
            ]);
        
            return redirect()->route('booking.confirmation')->with('success', 'Day visit payment successful! Booking confirmed. Check your email for details.');
        } else {
            // Handle regular booking
            $selectedActivities = session('selected_activities', []);

            // Add payment details to booking data for receipt
            $bookingData['payment_method'] = $request->payment_method;
            $bookingData['payment_date'] = now();
            $bookingData['transaction_id'] = 'TXN-' . strtoupper(uniqid());

            try {
                // Send receipt email with activities
                Log::info('Sending regular booking confirmation email to: ' . $bookingData['email']);
                Mail::to($bookingData['email'])->send(new BookingConfirmation($bookingData, $selectedActivities));
                Log::info('Regular booking confirmation email sent successfully to: ' . $bookingData['email']);
            } catch (\Exception $e) {
                // Log the error but continue to confirmation
                Log::error('Receipt email sending failed: ' . $e->getMessage());
                Log::error('Regular booking email error details: ', ['email' => $bookingData['email'], 'booking' => $bookingData]);
            }

            // Redirect to confirmation page
            return redirect()->route('booking.confirmation');
        }
    }

    public function widget()
    {
        // Assuming settings are stored in a Settings model or config
        // For now, using placeholder values
        $settings = (object) [
            'day_visit_capacity' => 10,
            'room_capacity' => 5,
            'overnight_capacity' => 3,
        ];

        // Default static inventory for demo
        $defaultInventory = [
            'day-visit' => [
                '2025-11-20' => 8, // 8 out of 10 available
                '2025-11-21' => 5,
                '2025-11-22' => 10, // fully available
            ],
            'room' => [
                '2025-11-20' => 3, // 3 out of 5 available
                '2025-11-21' => 2,
                '2025-11-22' => 5,
            ],
            'overnight' => [
                '2025-11-20' => 2, // 2 out of 3 available
                '2025-11-21' => 1,
                '2025-11-22' => 3,
            ],
        ];

        // Get inventory from session, or use default
        $remainingPerDateArray = session('inventory', $defaultInventory);

        return view('booking.widget', [
            'dayVisitCapacity'    => $settings->day_visit_capacity,
            'roomCapacity'        => $settings->room_capacity,
            'overnightCapacity'   => $settings->overnight_capacity,
            'inventory'           => $remainingPerDateArray, // optional
        ]);
    }

    public function staycation(Request $request)
    {
        // Handle POST request to set dates and accommodations
        if ($request->isMethod('post')) {
            $request->validate([
                'check_in' => 'sometimes|date|after:yesterday',
                'check_out' => 'sometimes|date|after:check_in',
                'accommodations' => 'sometimes|array',
            ]);

            $booking = session('booking', []);
            if ($request->has('check_in') && $request->has('check_out')) {
                $booking['checkin'] = $request->check_in;
                $booking['checkout'] = $request->check_out;
                $booking['booking_type'] = 'staycation';
                $booking['completed_steps'] = [1]; // Mark step 1 as completed
                $booking['current_step'] = 1;
                $booking['timestamp'] = now()->timestamp;
            }
            if ($request->has('accommodations')) {
                $booking['accommodations'] = $request->input('accommodations', []);
                $booking['completed_steps'] = array_unique(array_merge($booking['completed_steps'] ?? [], [2]));
                $booking['current_step'] = 2;
            }
            session(['booking' => $booking]);

            // Redirect based on request
            if ($request->input('proceed_to_activities')) {
                return redirect()->route('booking.staycationActivities');
            } else {
                return redirect()->route('booking.staycation');
            }
        }

        // Hardcoded staycations data with appropriate images
        $staycations = [
            [
                'id' => 1,
                'name' => 'Standard Glamping',
                'type' => 'glamping',
                'capacity' => 6,
                'adults' => 4,
                'children' => 2,
                'price' => 1200,
                'image' => asset('assets/images/carousel/Glamping-1.jpg'),
            ],
            [
                'id' => 2,
                'name' => 'Premium Glamping',
                'type' => 'glamping',
                'capacity' => 6,
                'adults' => 4,
                'children' => 2,
                'price' => 5500,
                'image' => asset('assets/images/carousel/Glamping-1.jpg'),
            ],
            [
                'id' => 3,
                'name' => 'Dome Tent',
                'type' => 'camping',
                'capacity' => 8,
                'adults' => 8,
                'children' => 0,
                'price' => 1200,
                'image' => asset('assets/images/carousel/Car-camping3.jpg'),
            ],
            [
                'id' => 4,
                'name' => 'Bell Tent',
                'type' => 'camping',
                'capacity' => 8,
                'adults' => 8,
                'children' => 0,
                'price' => 1800,
                'image' => asset('assets/images/carousel/Car-camping3.jpg'),
            ],
            [
                'id' => 5,
                'name' => 'BYOT',
                'type' => 'camping',
                'capacity' => 4,
                'adults' => 4,
                'children' => 2,
                'price' => 800,
                'image' => asset('assets/images/carousel/BYOT-2.jpg'),
            ],
            [
                'id' => 6,
                'name' => 'Car Camping',
                'type' => 'camping',
                'capacity' => 4,
                'adults' => 4,
                'children' => 2,
                'price' => 1500,
                'image' => asset('assets/images/carousel/Car-camping3.jpg'),
            ],
        ];

        // Get accommodation data from session or default
        $accommodationData = session('accommodation_data', []);

        // Check availability if dates are selected
        $availability = [];
        $booking = session('booking', []);
        $checkinDate = $booking['checkin'] ?? null;
        $checkoutDate = $booking['checkout'] ?? null;

        if ($checkinDate && $checkoutDate) {
            $staycationIds = array_column($staycations, 'id');
            // Call the checkAvailability method internally
            $availability = $this->checkAvailabilityInternal($checkinDate, $checkoutDate, $staycationIds);
        }

        // Pre-select accommodation if package is provided in query params
        if ($request->has('package')) {
            $packageName = urldecode($request->get('package'));
            $selectedStaycation = collect($staycations)->firstWhere('name', $packageName);
            if ($selectedStaycation) {
                $booking['accommodations'][0] = [
                    'staycation' => $selectedStaycation,
                    'guests' => $selectedStaycation['adults'] ?? 2, // Default to adults count
                ];
                session(['booking' => $booking]);
            }
        }

        return view('booking.staycation', compact('staycations', 'accommodationData', 'availability'));
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
        $request->validate([
            'checkin' => 'required|date|after:today',
            'checkout' => 'required|date|after:checkin',
        ]);

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
        $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'staycation_ids' => 'required|array',
            'staycation_ids.*' => 'integer',
        ]);

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

            // Check if today is available, if not, find next available date
            $defaultInventory = [
                'day-visit' => [
                    '2025-11-20' => 8,
                    '2025-11-21' => 5,
                    '2025-11-22' => 10,
                ],
            ];
            $inventory = session('inventory', $defaultInventory);

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
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'sometimes|date|after:yesterday',
            'guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        // Use session date if no booking_date provided
        $bookingDate = $validated['booking_date'] ?? session('selected_dayvisit_date');
        if (!$bookingDate) {
            return redirect()->route('booking.dayvisit')->with('error', 'Please select a booking date.');
        }

        $validated['booking_date'] = $bookingDate;
        $validated['type'] = 'day-visit';
        $validated['package'] = 'Day Visit (500 per person)';
        $validated['price'] = 500 * $validated['guests'];

        // Get current inventory
        $defaultInventory = [
            'day-visit' => ['2025-11-20' => 8, '2025-11-21' => 5, '2025-11-22' => 10],
        ];
        $inventory = session('inventory', $defaultInventory);

        $date = $validated['booking_date'];

        // Check availability
        if (isset($inventory['day-visit'][$date]) && $inventory['day-visit'][$date] <= 0) {
            return redirect()->back()->with('error', 'No availability for the selected date.');
        }

        // Deduct inventory
        if (isset($inventory['day-visit'][$date])) {
            $inventory['day-visit'][$date] = max(0, $inventory['day-visit'][$date] - 1);
            session(['inventory' => $inventory]);
        }

        // Store booking data in session
        session(['dayvisit_booking_data' => $validated]);

        // Clear the selected date from session
        session()->forget('selected_dayvisit_date');

        // Redirect to activities
        return redirect()->route('booking.dayvisitActivities');
    }

    public function dayvisitActivities()
    {
        $bookingData = session('dayvisit_booking_data');

        if (!$bookingData) {
            return redirect()->route('booking.dayvisit')->with('error', 'No booking data found. Please start booking again.');
        }

        $activities = $this->getActivitiesData();

        return view('booking.dayvisit-activities', compact('bookingData', 'activities'));
    }

    public function getDayvisitActivitiesApi()
    {
        $activities = $this->getActivitiesData();
        return response()->json($activities);
    }

    private function getActivitiesData()
    {
        // Centralized activities data from the activities page
        $activitiesByCategory = [
            'adventure' => [
                [
                    'id' => 1,
                    'name' => 'Go-Kart (15yo above)',
                    'description' => '4 rounds / 10 minutes (approx.), Age requirement: 15 years old and above',
                    'duration' => '10 minutes',
                    'price' => 500,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/go-cart2.jpg'),
                ],
                [
                    'id' => 2,
                    'name' => 'ATV (15yo above)',
                    'description' => 'Duration: 20 minutes, Age requirement: 15 years old and above',
                    'duration' => '20 minutes',
                    'price' => 800,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/atv.jpg'),
                ],
                [
                    'id' => 3,
                    'name' => 'Wet Inflatable Slide',
                    'description' => 'Minimum of 3 children or ₱1,050',
                    'duration' => '1 hour',
                    'price' => 350,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slide-n-go2.jpg'),
                ],
                [
                    'id' => 4,
                    'name' => 'Mini Kalesa',
                    'description' => 'Duration: 30 minutes, Weight limit: 200 lbs max',
                    'duration' => '30 minutes',
                    'price' => 600,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/minikalesa.jpg'),
                ],
                [
                    'id' => 5,
                    'name' => 'Horseback Riding',
                    'description' => 'Duration: 30 minutes',
                    'duration' => '30 minutes',
                    'price' => 600,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/our-farm2.jpg'),
                ],
            ],

            'sports' => [
                [
                    'id' => 6,
                    'name' => 'Mountain Bike',
                    'description' => 'Duration: 1 hour',
                    'duration' => '1 hour',
                    'price' => 300,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/bikes.jpg'),
                ],
                [
                    'id' => 7,
                    'name' => 'Archery Tag | Target',
                    'description' => 'Duration: 45 minutes',
                    'duration' => '45 minutes',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/archery.jpg'),
                ],
                [
                    'id' => 8,
                    'name' => 'Disc Golf',
                    'description' => 'Rate: ₱250 per game',
                    'duration' => '1 hour',
                    'price' => 250,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 9,
                    'name' => 'Kiteflying',
                    'description' => 'Duration: 1 hour',
                    'duration' => '1 hour',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 10,
                    'name' => 'Board Games / Cards',
                    'description' => 'Casual indoor board and card games.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 11,
                    'name' => 'Sandy Pickle',
                    'description' => 'Sand pickleball-style game.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
                [
                    'id' => 12,
                    'name' => 'Frisbee',
                    'description' => 'Casual throwing game in the field.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
                [
                    'id' => 13,
                    'name' => 'Field Volleyball',
                    'description' => 'Outdoor volleyball on the grass field.',
                    'duration' => '1 hour',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/games-grounds4.jpg'),
                ],
            ],

            'rrcamp' => [
                [
                    'id' => 14,
                    'name' => 'Kawa Bath',
                    'description' => 'Duration: 1 hour (1–4 persons)',
                    'duration' => '1 hour',
                    'price' => 1000,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Kawa bath.jpg'),
                ],
                [
                    'id' => 15,
                    'name' => 'Bonfire & S\'mores',
                    'description' => '₱350 bonfire, ₱450 s\'mores (25 pcs)',
                    'duration' => '2 hours',
                    'price' => 350,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/bonfire.jpg'),
                ],
                [
                    'id' => 16,
                    'name' => 'Hiking',
                    'description' => 'Enjoy scenic nature trails around the property.',
                    'duration' => '2 hours',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/slides CALAIA-01.png'),
                ],
                [
                    'id' => 17,
                    'name' => 'Fishing',
                    'description' => '1 hour with cup of bait, ₱250 per rod',
                    'duration' => '1 hour',
                    'price' => 250,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
                ],
                [
                    'id' => 18,
                    'name' => 'Outdoor Movie',
                    'description' => 'Strictly 6–9 PM only',
                    'duration' => '3 hours',
                    'price' => 1500,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
                ],
            ],

            'farm' => [
                [
                    'id' => 19,
                    'name' => 'Tree Planting',
                    'description' => '₱200 per tree, Includes name label on the tree',
                    'duration' => '1 hour',
                    'price' => 200,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-1164.jpg'),
                ],
                [
                    'id' => 20,
                    'name' => 'Farm Tour / Animal Feeding',
                    'description' => 'Guided tour with friendly farm animals',
                    'duration' => '1 hour',
                    'price' => 150,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-880.jpg'),
                ],
                [
                    'id' => 21,
                    'name' => 'Veggie Picking',
                    'description' => 'Prices based on market price that day',
                    'duration' => '30 minutes',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/Retreat-880.jpg'),
                ],
                [
                    'id' => 22,
                    'name' => 'Animal Encounters',
                    'description' => 'Close-up interactions with farm animals',
                    'duration' => '30 minutes',
                    'price' => 0,
                    'availability' => 'Available',
                    'image' => asset('assets/images/carousel/our-farm2.jpg'),
                ],
            ],
        ];

        // Flatten all activities into a single array
        $activities = [];
        foreach ($activitiesByCategory as $category => $categoryActivities) {
            $activities = array_merge($activities, $categoryActivities);
        }

        return $activities;
    }

    public function storeDayvisitActivities(Request $request)
    {
        $request->validate([
            'activities' => 'required|array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined',
        ]);

        $bookingData = session('dayvisit_booking_data');

        if (!$bookingData) {
            return response()->json(['success' => false, 'message' => 'No booking data found.'], 400);
        }

        // Store selected activities in session
        session(['dayvisit_selected_activities' => $request->activities]);

        return response()->json(['success' => true, 'message' => 'Activities saved successfully.']);
    }

    public function dayvisitPayment()
    {
        $bookingData = session('dayvisit_booking_data');

        if (!$bookingData) {
            return redirect()->route('booking.dayvisit')->with('error', 'No booking data found. Please start booking again.');
        }

        return view('booking.dayvisit-payment', compact('bookingData'));
    }
}