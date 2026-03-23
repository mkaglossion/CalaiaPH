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

        // Check inventory
        $food = Food::find($validated['food_id']);
        if (!$food) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found'
            ], 404);
        }

        $totalGuests = $validated['adults'];
        
        if ($food->is_inventory_managed && $food->stock_quantity < $totalGuests) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $food->stock_quantity . ' guests available.'
            ], 422);
        }

        // Create reservation
        $reservation = DiningReservation::create(array_merge($validated, ['status' => 'confirmed']));
        
        // Load the food relationship
        $reservation->load('food');

        // Deduct from inventory
        if ($food->is_inventory_managed) {
            $food->decrement('stock_quantity', $totalGuests);
        }

        // Send confirmation email - Immediately (no queue delay)
        try {
            Mail::to($validated['email'])
                ->send(new DiningReservationConfirmation($reservation));
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
    }
}
