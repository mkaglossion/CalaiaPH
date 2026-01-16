<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Staycation;
use App\Models\StaycationInventory;

class BookingCalendarController extends Controller
{
    public function show(Request $request)
    {
        $start = Carbon::today();
        $end = (clone $start)->addMonths(6);

        // Build availability from staycation inventory — per-date total across ALL rooms
        $availability = [];
        $maxQty = 0;

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(Carbon::today())) {
                $availability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available quantity for this date from inventory table
            // This sums available_quantity across all staycations for this date
            $totalAvailable = StaycationInventory::where('date', $key)
                ->sum('available_quantity');

            // If no inventory records exist for this date, fall back to total base quantity
            if ($totalAvailable == 0) {
                $totalAvailable = Staycation::sum('quantity');
            }

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];

            // Track max for threshold calculation
            if ($totalAvailable > $maxQty) {
                $maxQty = $totalAvailable;
            }
        }

        // Get total quantity from staycations for fallback legend
        $totalQuantity = Staycation::sum('quantity');

        // Use max from inventory if available, otherwise use base total
        if ($maxQty === 0) {
            $maxQty = $totalQuantity;
        }

        // You can pass initial selected dates if you want:
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');

        return view('booking.calendar', compact('availability', 'checkin', 'checkout', 'totalQuantity', 'maxQty'));
    }
}
