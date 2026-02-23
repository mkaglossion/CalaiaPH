<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Models\DayTour;
use App\Models\DayTourInventory;
use App\Models\PeakPeriod;
use App\Models\PeakType;

class BookingCalendarController extends Controller
{
    public function show(Request $request)
    {
        // Determine booking type: 'staycation' (default) or 'daytour'
        $type = $request->query('type', 'staycation');

        if ($type === 'daytour') {
            return $this->showDayTourCalendar($request);
        }

        return $this->showStaycationCalendar($request);
    }

    /**
     * Show staycation calendar with date range selection
     */
    private function showStaycationCalendar(Request $request)
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

        // Determine season for pricing
        $season = $this->determineSeasonForCalendar();

        // You can pass initial selected dates if you want:
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');

        $bookingType = 'staycation';

        return view('booking.calendar', compact('availability', 'checkin', 'checkout', 'totalQuantity', 'maxQty', 'season', 'bookingType'));
    }

    /**
     * Show day tour calendar with single date selection
     */
    private function showDayTourCalendar(Request $request)
    {
        $start = Carbon::today();
        $end = (clone $start)->addMonths(6);

        // Get all active day tours
        $dayTours = DayTour::where('is_active', true)->get();

        // Build availability for each day tour package
        $availability = [];
        $maxQty = 0;
        $totalQuantity = 0;

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(Carbon::today())) {
                $availability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0,
                    'packages' => []
                ];
                continue;
            }

            // Get total available groups across all day tours for this date
            $totalAvailable = DayTourInventory::where('date', $key)
                ->where('is_blocked', false)
                ->sum('available_groups');

            // If no inventory records exist, calculate from day tour defaults
            if ($totalAvailable == 0) {
                $totalAvailable = $dayTours->sum('max_groups_per_day');
            }

            // Get per-package availability
            $packages = [];
            foreach ($dayTours as $tour) {
                $inventory = DayTourInventory::where('day_tour_id', $tour->id)
                    ->where('date', $key)
                    ->first();

                $availableGroups = $inventory ? $inventory->available_groups : $tour->max_groups_per_day;
                
                $packages[] = [
                    'id' => $tour->id,
                    'name' => $tour->name,
                    'price' => $tour->price,
                    'available_groups' => $availableGroups,
                    'is_available' => $availableGroups > 0 && !$inventory?->is_blocked
                ];
            }

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable,
                'packages' => $packages
            ];

            // Track max for threshold calculation
            if ($totalAvailable > $maxQty) {
                $maxQty = $totalAvailable;
            }

            $totalQuantity += $totalAvailable;
        }

        // Use max from inventory if available, otherwise use base total
        if ($maxQty === 0) {
            $maxQty = $dayTours->sum('max_groups_per_day');
        }

        // Determine season for pricing
        $season = $this->determineSeasonForCalendar();

        // Single date selection for day tours
        $selectedDate = $request->query('date');

        $bookingType = 'daytour';
        $dayToursList = $dayTours;

        return view('booking.calendar', compact('availability', 'selectedDate', 'totalQuantity', 'maxQty', 'season', 'bookingType', 'dayToursList'));
    }

    private function determineSeasonForCalendar()
    {
        // Use today's date to determine season
        $today = Carbon::today();

        // Check for peak periods
        $peakPeriod = PeakPeriod::where('start_date', '<=', $today->format('Y-m-d'))
            ->where('end_date', '>=', $today->format('Y-m-d'))
            ->with('peakType')
            ->orderByRaw('(SELECT priority FROM peak_types WHERE peak_types.id = peak_periods.peak_type_id) DESC')
            ->first();

        if ($peakPeriod) {
            $seasonName = $peakPeriod->peakType->name;
            $seasonKeyMap = [
                'Non-Peak' => 'nonPeak',
                'Peak' => 'peak',
                'Super-Peak' => 'superPeak'
            ];
            return $seasonKeyMap[$seasonName] ?? 'nonPeak';
        }

        return 'nonPeak';
    }
}
