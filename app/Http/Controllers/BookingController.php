<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmation;
use DateTime;

class BookingController extends Controller
{
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

        return view('booking.reservation', compact('type', 'package', 'price', 'settings', 'inventory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'booking_date' => 'required|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:50',
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

        // Hardcoded activities for demo
        $activities = [
            [
                'id' => 1,
                'name' => 'Farm Tour',
                'description' => 'Guided tour of our organic farm, learning about sustainable farming practices.',
                'duration' => '2 hours',
                'price' => 500,
                'availability' => 'Available',
            ],
            [
                'id' => 2,
                'name' => 'Cooking Workshop',
                'description' => 'Hands-on cooking class using fresh, organic ingredients from our farm.',
                'duration' => '3 hours',
                'price' => 1200,
                'availability' => 'Available',
            ],
            [
                'id' => 3,
                'name' => 'Biking Adventure',
                'description' => 'Explore the scenic trails around the farm on our mountain bikes.',
                'duration' => '1.5 hours',
                'price' => 300,
                'availability' => 'Limited spots',
            ],
            [
                'id' => 4,
                'name' => 'Photography Session',
                'description' => 'Professional photography session capturing the beauty of our farm.',
                'duration' => '2 hours',
                'price' => 800,
                'availability' => 'Available',
            ],
        ];

        return view('booking.activities', compact('bookingData', 'activities'));
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

        // Hardcoded activities for demo
        $activities = [
            [
                'id' => 1,
                'name' => 'Farm Tour',
                'description' => 'Guided tour of our organic farm, learning about sustainable farming practices.',
                'duration' => '2 hours',
                'price' => 500,
                'availability' => 'Available',
            ],
            [
                'id' => 2,
                'name' => 'Cooking Workshop',
                'description' => 'Hands-on cooking class using fresh, organic ingredients from our farm.',
                'duration' => '3 hours',
                'price' => 1200,
                'availability' => 'Available',
            ],
            [
                'id' => 3,
                'name' => 'Biking Adventure',
                'description' => 'Explore the scenic trails around the farm on our mountain bikes.',
                'duration' => '1.5 hours',
                'price' => 300,
                'availability' => 'Limited spots',
            ],
            [
                'id' => 4,
                'name' => 'Photography Session',
                'description' => 'Professional photography session capturing the beauty of our farm.',
                'duration' => '2 hours',
                'price' => 800,
                'availability' => 'Available',
            ],
        ];

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

        if (!$bookingData && empty($staycationBooking)) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Determine booking type
        $isStaycation = !empty($staycationBooking) && isset($staycationBooking['booking_type']) && $staycationBooking['booking_type'] === 'staycation';

        if ($isStaycation) {
            // Handle staycation booking
            $selectedActivities = $staycationBooking['selected_activities'] ?? [];

            // Add payment details to booking data for receipt
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
                'capacity' => 2,
                'adults' => 2,
                'children' => 0,
                'price' => 1200,
                'image' => asset('assets/images/carousel/Glamping-1.jpg'),
            ],
            [
                'id' => 2,
                'name' => 'Premium Glamping',
                'type' => 'glamping',
                'capacity' => 4,
                'adults' => 4,
                'children' => 2,
                'price' => 5500,
                'image' => asset('assets/images/carousel/Glamping-1.jpg'),
            ],
            [
                'id' => 3,
                'name' => 'Dome Tent',
                'type' => 'camping',
                'capacity' => 2,
                'adults' => 2,
                'children' => 0,
                'price' => 1200,
                'image' => asset('assets/images/carousel/Car-camping3.jpg'),
            ],
            [
                'id' => 4,
                'name' => 'Bell Tent',
                'type' => 'camping',
                'capacity' => 4,
                'adults' => 4,
                'children' => 2,
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
}