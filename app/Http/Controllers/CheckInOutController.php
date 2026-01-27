<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class CheckInOutController extends Controller
{
    /**
     * Display the check-in/out page for the authenticated user.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Fetch active bookings for the user: confirmed status, current date within checkin/checkout
        $today = now()->toDateString();
        $activeBookings = Booking::where('customer_email', $user->email)
            ->where('status', 'confirmed')
            ->where('checkin_date', '<=', $today)
            ->where('checkout_date', '>=', $today)
            ->orderBy('checkin_date', 'asc')
            ->get();

        return view('checkinout', compact('activeBookings'));
    }

    /**
     * Handle check-in for a booking.
     *
     * @param \App\Models\Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkin(Booking $booking)
    {
        $user = Auth::user();
        if (!$user || $booking->customer_email !== $user->email) {
            abort(403);
        }

        if (($booking->checkin_status ?? 'pending') === 'pending') {
            $booking->update([
                'checkin_status' => 'checked_in',
                'checkin_timestamp' => now()
            ]);

            return redirect()->route('checkinout')->with('success', 'Checked in successfully!');
        } elseif (($booking->checkin_status ?? 'pending') === 'checked_in' && !$booking->checkin_timestamp) {
            // Fix missing timestamp for already checked-in bookings
            $booking->update(['checkin_timestamp' => now()]);
            return redirect()->route('checkinout')->with('success', 'Check-in timestamp updated!');
        }

        return back()->with('error', 'Unable to check in.');
    }

    /**
     * Handle check-out for a booking.
     *
     * @param \App\Models\Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(Booking $booking)
    {
        $user = Auth::user();
        if (!$user || $booking->customer_email !== $user->email) {
            abort(403);
        }

        if ($booking->checkin_status === 'checked_in') {
            $updateData = [
                'checkin_status' => 'checked_out',
                'status' => 'completed',
                'checkout_timestamp' => now()
            ];
            
            // If checkin_timestamp is missing, set it too
            if (!$booking->checkin_timestamp) {
                $updateData['checkin_timestamp'] = now();
            }
            
            $booking->update($updateData);
            
            return back()->with('success', 'Successfully checked out!');
        } elseif ($booking->checkin_status === 'checked_out' && !$booking->checkin_timestamp && $booking->checkout_timestamp) {
            // Fix missing checkin timestamp for already checked-out bookings
            $checkinTime = $booking->checkout_timestamp->copy()->subHours(2);
            $booking->update(['checkin_timestamp' => $checkinTime]);
            return back()->with('success', 'Check-in timestamp updated!');
        }

        return back()->with('error', 'Unable to check out.');
    }

    /**
     * Handle the next step after check-in (redirect to activities).
     *
     * @param \App\Models\Booking $booking
     * @return \Illuminate\Http\RedirectResponse
     */
    public function nextAfterCheckin(Booking $booking)
    {
        $user = Auth::user();
        if (!$user || $booking->customer_email !== $user->email) {
            abort(403);
        }

        // Since activities are already selected during booking, just redirect back
        return redirect()->route('checkinout')->with('success', 'Welcome! You are now checked in.');
    }
}
