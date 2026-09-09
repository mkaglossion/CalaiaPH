<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\AddOnTransaction;
use App\Models\Booking;
use App\Services\AddOnExpirationService;

class ProfileController extends Controller
{
    public function show(AddOnExpirationService $expirationService)
    {
        $user = Auth::user();

        $bookings = Booking::query()
            ->ownedBy($user)
            ->latest()
            ->get();

        $expirationService->expireStaleForBookings($bookings->modelKeys());
        $bookings->load([
            'addOnTransactions' => fn ($query) => $query->visibleToCustomer(),
        ]);

        $totalBookings = $bookings->count();
        $confirmedBookings = $bookings->where('status', 'confirmed')->count();
        $originalBookingsPaid = $bookings
            ->where('payment_status', 'paid')
            ->sum(fn (Booking $booking) => (float) $booking->total_amount);
        $addOnsPaid = $bookings
            ->flatMap->addOnTransactions
            ->where('payment_status', 'paid')
            ->sum(fn (AddOnTransaction $transaction) => (float) $transaction->total_amount);
        $totalSpent = $originalBookingsPaid + $addOnsPaid;

        return view('profile', compact('bookings', 'totalBookings', 'totalSpent', 'confirmedBookings'));
    }

    public function bookingDetails(Booking $booking, AddOnExpirationService $expirationService)
    {
        Gate::authorize('view', $booking);

        $expirationService->expireStaleForBookings([$booking->id]);
        $booking->load([
            'addOnTransactions' => fn ($query) => $query->visibleToCustomer(),
        ]);

        return view('profile.booking-details', compact('booking'));
    }

    public function addOnDetails(Booking $booking, AddOnTransaction $addOnTransaction)
    {
        Gate::authorize('view', $booking);

        $ownedAddOn = $booking->addOnTransactions()
            ->whereKey($addOnTransaction->getKey())
            ->firstOrFail();

        return redirect()->route('profile.bookings.show', $booking)
            ->withFragment('add-on-' . $ownedAddOn->id);
    }

    public function edit()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'mobile_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
        ]);

        DB::table('users')->where('id', Auth::id())->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return redirect()->to(route('profile.show') . '#edit')->with('success', 'Profile updated successfully!');
    }
}
