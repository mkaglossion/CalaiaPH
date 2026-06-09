<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\PackageBookingConfirmation;
use Illuminate\Support\Facades\Mail;

class PackageBookingController extends Controller
{
    /**
     * Create a new package booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'total_guests' => 'required|integer|min:1',
            'session' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'email' => 'required|email',
            'special_requests' => 'nullable|string',
            'total_amount' => 'nullable|numeric|min:0'
        ]);

        try {
            // Check package exists and guest limit
            $package = Package::findOrFail($validated['package_id']);
            
            if ($validated['total_guests'] > $package->max_guests) {
                return response()->json([
                    'success' => false,
                    'message' => 'Number of guests exceeds package capacity'
                ], 422);
            }

            // Compute total on server for consistency and integrity.
            $totalAmount = $package->pricing_type === 'per_group'
                ? (float) $package->price
                : (float) $package->price * (int) $validated['total_guests'];

            // Create booking
            $booking = PackageBooking::create([
                'booking_reference' => 'PB-' . strtoupper(Str::random(8)),
                'package_id' => $validated['package_id'],
                'booking_date' => $validated['booking_date'],
                'total_guests' => $validated['total_guests'],
                'groups_booked' => 1, // default 1 group per booking
                'session' => $validated['session'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'special_requests' => $validated['special_requests'],
                'total_amount' => $totalAmount,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating package booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking'
            ], 500);
        }
    }

    /**
     * Get booking details
     */
    public function show($id)
    {
        try {
            $booking = PackageBooking::with('package')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }
    }

    /**
     * Update booking after successful payment
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:paid,failed,cancelled',
            'payment_reference' => 'nullable|string'
        ]);

        try {
            $booking = PackageBooking::findOrFail($id);

            $booking->update([
                'payment_status' => $validated['payment_status'],
                'payment_reference' => $validated['payment_reference'],
                'status' => $validated['payment_status'] === 'paid' ? 'confirmed' : 'cancelled'
            ]);

            // Send confirmation email if payment successful
            if ($validated['payment_status'] === 'paid') {
                Mail::to($booking->email)->queue(new PackageBookingConfirmation($booking));
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating booking payment status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking'
            ], 500);
        }
    }
}
