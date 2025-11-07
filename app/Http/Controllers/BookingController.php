<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->get('type');
        $package = $request->get('package');
        $price = $request->get('price');

        // Clean the price to remove currency symbols and get numeric value
        $price = preg_replace('/[^\d.]/', '', $price);

        return view('booking.reservation', compact('type', 'package', 'price'));
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
            'type' => 'required|string',
            'package' => 'required|string',
            'price' => 'required|numeric',
        ]);

        // Here you would typically save to database
        // For now, we'll just send a confirmation email

        try {
            Mail::to($validated['email'])->send(new BookingConfirmation($validated));

            return redirect()->back()->with('success', 'Booking request submitted successfully! We will contact you soon.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to submit booking. Please try again.');
        }
    }
}