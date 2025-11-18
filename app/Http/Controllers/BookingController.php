<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->get('type', 'day-visit');
        $allowedTypes = ['day-visit', 'room', 'overnight'];
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
            'booking_date' => 'required|date|after:today',
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

        // Check availability
        if (!isset($inventory[$type][$date]) || $inventory[$type][$date] <= 0) {
            return redirect()->back()->with('error', 'No availability for the selected date and type.');
        }

        // Deduct inventory for the booked date
        if (isset($inventory[$type][$date])) {
            $inventory[$type][$date] = max(0, $inventory[$type][$date] - 1);
            session(['inventory' => $inventory]);
        }

        // Store booking data in session for payment
        session(['booking_data' => $validated]);

        // Redirect to payment page
        return redirect()->route('booking.payment');
    }

    public function payment()
    {
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        return view('booking.payment', compact('bookingData'));
    }

    public function processPayment(Request $request)
    {
        $bookingData = session('booking_data');

        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start booking again.');
        }

        // Simulate payment processing
        // In real implementation, integrate with payment gateway

        try {
            // Send confirmation email
            Mail::to($bookingData['email'])->send(new BookingConfirmation($bookingData));

            // Clear session
            session()->forget('booking_data');

            return redirect()->route('home')->with('success', 'Payment successful! Booking confirmed. Check your email for details.');
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Payment failed. Please try again.');
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
        // Handle POST request to set dates
        if ($request->isMethod('post')) {
            $request->validate([
                'check_in' => 'required|date|after:yesterday',
                'check_out' => 'required|date|after:check_in',
            ]);

            $booking = session('booking', []);
            $booking['checkin'] = $request->check_in;
            $booking['checkout'] = $request->check_out;
            $booking['booking_type'] = 'staycation';
            $booking['completed_steps'] = [1]; // Mark step 1 as completed
            $booking['current_step'] = 1;
            $booking['timestamp'] = now()->timestamp;
            session(['booking' => $booking]);

            // Redirect to GET version to display the page
            return redirect()->route('booking.staycation');
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