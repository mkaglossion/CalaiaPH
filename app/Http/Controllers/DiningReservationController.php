<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\DiningReservation;
use App\Mail\DiningReservationConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DiningReservationController extends Controller
{
    /**
     * Store a dining reservation
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'food_id' => 'required|exists:foods,id',
                'meal_period' => 'required|in:breakfast,lunch,snack,dinner,supper',
                'reservation_date' => 'required|date|after_or_equal:today',
                'reservation_time' => 'required|date_format:H:i',
                'adults' => 'required|integer|min:1|max:20',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'email' => 'required|email',
                'special_requests' => 'nullable|string|max:1000'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Check inventory and get food
            $food = Food::find($validated['food_id']);
            if (!$food) {
                return response()->json([
                    'success' => false,
                    'message' => 'Food item not found'
                ], 404);
            }

            $totalGuests = $validated['adults'];
            // Calculate total amount based on selected food price and number of guests
            $exclusiveTotal = (float) $food->price * (int) $totalGuests;
            // Use VatService to add VAT to exclusive price (store gross amount)
            $vatService = app(\App\Services\VatService::class);
            $grossTotal = $vatService->addVat($exclusiveTotal);
            
            if ($food->is_inventory_managed && $food->stock_quantity < $totalGuests) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $food->stock_quantity . ' guests available.'
                ], 422);
            }

            // Create reservation (persist denormalized food name and total amount)
            $reservation = DiningReservation::create(array_merge($validated, [
                'status' => 'confirmed',
                'food_name' => $food->name,
                'total_amount' => $grossTotal,
                // use enum-compatible default
                'payment_status' => 'pending'
            ]));
            
            // Reload reservation with food relationship
            $reservation->refresh();
            $reservation->load('food');

            // Deduct from inventory
            if ($food->is_inventory_managed) {
                $food->decrement('stock_quantity', $totalGuests);
            }

            // Send confirmation email
            try {
                Mail::to($validated['email'])
                    ->queue(new DiningReservationConfirmation($reservation));
                Log::info('Dining reservation email sent successfully to: ' . $validated['email']);
            } catch (\Exception $e) {
                // Log error but don't fail the reservation
                Log::error('Failed to send dining reservation confirmation email to ' . $validated['email'] . ': ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully! A confirmation email has been sent to ' . $validated['email'] . '. Please check your inbox and spam folder.',
                'reservation_id' => $reservation->id
            ]);
        } catch (\Exception $e) {
            Log::error('Dining reservation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show day-visit reservation form
     */
    public function dayVisitForm()
    {
        $dayMenus = Food::where('available_for_day_visit', true)
            ->where('is_active', true)
            ->orderBy('food_category')
            ->orderBy('name')
            ->get()
            ->groupBy('food_category');

        return view('dining.day-visit-reservation', ['foods' => $dayMenus]);
    }

    /**
     * Store day-visit dining reservation
     */
    public function storeDayVisit(Request $request)
    {
        try {
            $validated = $request->validate([
                'reservation_date' => 'required|date|after_or_equal:today',
                'reservation_time' => 'required|date_format:H:i',
                'guests' => 'required|integer|min:1|max:50',
                'selected_foods' => 'required|array|min:1',
                'selected_foods.*.id' => 'required|exists:foods,id',
                'selected_foods.*.quantity' => 'required|integer|min:1',
                'customer_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'notes' => 'nullable|string|max:500'
            ]);

            // Calculate total amount
            $totalAmount = 0;
            $foodDetails = [];

            foreach ($validated['selected_foods'] as $selectedFood) {
                $food = Food::find($selectedFood['id']);
                if (!$food) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Food item not found'
                    ], 404);
                }

                $quantity = $selectedFood['quantity'];
                $itemTotal = (float) $food->price * $quantity;
                $totalAmount += $itemTotal;

                $foodDetails[] = [
                    'id' => $food->id,
                    'name' => $food->name,
                    'price' => $food->price,
                    'quantity' => $quantity,
                    'subtotal' => $itemTotal
                ];

                // Check inventory
                if ($food->is_inventory_managed && $food->stock_quantity < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Only {$food->stock_quantity} {$food->name} available"
                    ], 422);
                }
            }

            // Add VAT
            $vatService = app(\App\Services\VatService::class);
            $grossTotal = $vatService->addVat($totalAmount);

            // Create reservation
            $reservation = DiningReservation::create([
                'type' => 'day_visit',
                'reservation_date' => $validated['reservation_date'],
                'reservation_time' => $validated['reservation_time'],
                'guests' => $validated['guests'],
                'selected_foods' => $validated['selected_foods'],
                'customer_name' => $validated['customer_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $grossTotal,
                'status' => 'pending',
                'reservation_data' => [
                    'food_details' => $foodDetails,
                    'subtotal' => $totalAmount,
                    'vat_inclusive' => $grossTotal
                ]
            ]);

            // Deduct from inventory
            foreach ($foodDetails as $food_item) {
                $food = Food::find($food_item['id']);
                if ($food && $food->is_inventory_managed) {
                    $food->decrement('stock_quantity', $food_item['quantity']);
                }
            }

            // Send to Messenger (optional - can integrate messenger API here)
            Log::info('Day-visit dining reservation created', [
                'reservation_id' => $reservation->id,
                'customer' => $reservation->customer_name,
                'date' => $reservation->reservation_date,
                'guests' => $reservation->guests
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservation sent! We\'ll confirm via Messenger shortly.',
                'reservation_id' => $reservation->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Day-visit reservation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prefill booking session for overnight stay from dining modal.
     * Accepts minimal fields and stores a single selected_foods entry in session,
     * then marks booking intent so the booking flow picks it up.
     */
    public function prefillBooking(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'selected_foods' => 'nullable|array',
                'selected_foods.*.food_id' => 'required_with:selected_foods|integer|exists:foods,id',
                'selected_foods.*.quantity' => 'nullable|integer|min:1',

                // fallback single-item fields
                'food_id' => 'nullable|integer|exists:foods,id',
                'meal_period' => 'nullable|in:breakfast,lunch,snack,dinner,supper',
                'date' => 'nullable|date',
                'time' => 'nullable',
                'adults' => 'nullable|integer|min:1|max:50',
                'first_name' => 'nullable|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:30',
                'replace' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $selectedFoods = [];
            $replace = isset($data['replace']) ? (bool) $data['replace'] : false;

            if (!empty($data['selected_foods']) && is_array($data['selected_foods'])) {
                foreach ($data['selected_foods'] as $item) {
                    $fid = (int) ($item['food_id'] ?? $item['id'] ?? 0);
                    $foodModel = \App\Models\Food::find($fid);
                    $selectedFoods[] = [
                        'food_id' => $fid,
                        'quantity' => isset($item['quantity']) ? (int)$item['quantity'] : (isset($item['adults']) ? (int)$item['adults'] : 1),
                        'meal_period' => $item['meal_period'] ?? $data['meal_period'] ?? null,
                        'date' => $item['date'] ?? $data['date'] ?? null,
                        'time' => $item['time'] ?? $data['time'] ?? null,
                        'first_name' => $item['first_name'] ?? $data['first_name'] ?? null,
                        'last_name' => $item['last_name'] ?? $data['last_name'] ?? null,
                        'email' => $item['email'] ?? $data['email'] ?? null,
                        'phone' => $item['phone'] ?? $data['phone'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'name' => $foodModel ? $foodModel->name : null,
                        'price' => $foodModel ? $foodModel->price : ($item['price'] ?? null),
                    ];
                }
            } elseif (!empty($data['food_id'])) {
                $foodModel = \App\Models\Food::find((int)$data['food_id']);
                $selectedFoods[] = [
                    'food_id' => (int) $data['food_id'],
                    'quantity' => isset($data['adults']) ? (int)$data['adults'] : 1,
                    'meal_period' => $data['meal_period'] ?? null,
                    'date' => $data['date'] ?? null,
                    'time' => $data['time'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'name' => $foodModel ? $foodModel->name : null,
                    'price' => $foodModel ? $foodModel->price : ($data['price'] ?? null),
                ];
            }

            // Ensure booking array exists in session and preserve other values
            $booking = session('booking', []);
            $booking['intent'] = 'dining_included';

            // Merge or replace existing session selected_foods
            $existing = session('selected_foods', []);
            if ($replace) {
                $merged = $selectedFoods;
            } else {
                // Merge quantities for same food_id
                $merged = $existing;
                foreach ($selectedFoods as $inc) {
                    $found = false;
                    foreach ($merged as &$m) {
                        if ((int)($m['food_id'] ?? $m['id'] ?? 0) === (int)($inc['food_id'] ?? $inc['id'] ?? 0)) {
                            $m['quantity'] = (int)($m['quantity'] ?? 0) + (int)($inc['quantity'] ?? 0);
                            // overwrite other props if provided
                            $m['meal_period'] = $inc['meal_period'] ?? $m['meal_period'] ?? null;
                            $m['date'] = $inc['date'] ?? $m['date'] ?? null;
                            $m['time'] = $inc['time'] ?? $m['time'] ?? null;
                            $m['first_name'] = $inc['first_name'] ?? $m['first_name'] ?? null;
                            $m['last_name'] = $inc['last_name'] ?? $m['last_name'] ?? null;
                            $m['email'] = $inc['email'] ?? $m['email'] ?? null;
                            $m['phone'] = $inc['phone'] ?? $m['phone'] ?? null;
                            $m['notes'] = $inc['notes'] ?? $m['notes'] ?? null;
                            $found = true;
                            break;
                        }
                    }
                    unset($m);
                    if (!$found) {
                        $merged[] = $inc;
                    }
                }
            }

            if (!empty($merged)) {
                // Ensure each merged item has name/price denormalized for client use
                foreach ($merged as &$m) {
                    if (isset($m['food_id']) && empty($m['name'])) {
                        $fm = \App\Models\Food::find((int)$m['food_id']);
                        if ($fm) {
                            $m['name'] = $fm->name;
                            $m['price'] = $fm->price;
                        }
                    }
                }
                unset($m);

                $booking['selected_foods'] = $merged;
                session(['selected_foods' => $merged]);
            }

            session(['booking' => $booking]);
            // Flash a short message so booking page can show a toast after redirect
            session()->flash('booking_prefill_saved', 'Your meal selection was saved — you can modify it here.');

            return response()->json(['success' => true, 'message' => 'Prefill saved to session']);
        } catch (\Throwable $e) {
            Log::error('Dining prefill error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'input' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'Server error while saving prefill'], 500);
        }
    }
}
