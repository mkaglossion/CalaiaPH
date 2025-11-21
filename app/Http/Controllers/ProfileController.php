<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // TODO: Implement actual booking statistics
        $totalBookings = 0; // Count of user's bookings
        $totalSpent = 0; // Total amount spent
        $confirmedBookings = 0; // Count of confirmed bookings

        return view('profile', compact('totalBookings', 'totalSpent', 'confirmedBookings'));
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

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }
}
