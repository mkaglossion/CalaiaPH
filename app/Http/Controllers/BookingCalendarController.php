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
use App\Models\BookingSetting;

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
     * Only shows dates that have inventory configured in the admin panel
     */
    private function showStaycationCalendar(Request $request)
    {
        $today = Carbon::today();

        // Get inventory dates - only dates that have explicit inventory records
        // No future limit - shows whatever dates admin configured
        $earliestDate = StaycationInventory::where('date', '>=', $today)
            ->min('date');
        $latestDate = StaycationInventory::where('date', '>=', $today)
            ->max('date');

        // If no inventory configured at all
        if (!$earliestDate || !$latestDate) {
            $bookingStartDate = null;
            $bookingEndDate = null;
            $availability = [];
            $maxQty = 0;
            $totalQuantity = Staycation::sum('quantity');

            $season = $this->determineSeasonForCalendar();
            $checkin = $request->query('checkin');
            $checkout = $request->query('checkout');
            $bookingType = 'staycation';
            $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

            return view('booking.calendar', compact(
                'availability', 'checkin', 'checkout', 'totalQuantity',
                'maxQty', 'season', 'bookingType', 'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
            ));
        }

        // Get all unique dates that have inventory configured
        $inventoryDates = StaycationInventory::whereBetween('date', [$earliestDate, $latestDate])
            ->distinct()
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // If no inventory configured, show message or fallback to empty range
        if (empty($inventoryDates)) {
            $bookingStartDate = null;
            $bookingEndDate = null;
            $availability = [];
            $maxQty = 0;
            $totalQuantity = Staycation::sum('quantity');

            $season = $this->determineSeasonForCalendar();
            $checkin = $request->query('checkin');
            $checkout = $request->query('checkout');
            $bookingType = 'staycation';
            $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

            return view('booking.calendar', compact(
                'availability', 'checkin', 'checkout', 'totalQuantity',
                'maxQty', 'season', 'bookingType', 'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
            ));
        }

        // Get min and max dates for calendar navigation
        $bookingStartDate = min($inventoryDates);
        $bookingEndDate = max($inventoryDates);

        // Build availability ONLY for dates that have inventory records
        $availability = [];
        $maxQty = 0;

        foreach ($inventoryDates as $dateStr) {
            $d = Carbon::parse($dateStr);

            // Skip past dates
            if ($d->lt(Carbon::today())) {
                $availability[$dateStr] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available quantity for this date from inventory table
            $totalAvailable = StaycationInventory::where('date', $dateStr)
                ->sum('available_quantity');

            $availability[$dateStr] = [
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
        $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

        return view('booking.calendar', compact(
            'availability', 'checkin', 'checkout', 'totalQuantity',
            'maxQty', 'season', 'bookingType', 'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
        ));
    }

    /**
     * Show day tour calendar with single date selection
     * Only shows dates that have inventory configured in the admin panel
     */
    private function showDayTourCalendar(Request $request)
    {
        $today = Carbon::today();

        // Get inventory dates - only dates that have explicit inventory records
        // No future limit - shows whatever dates admin configured
        $earliestDate = DayTourInventory::where('date', '>=', $today)
            ->min('date');
        $latestDate = DayTourInventory::where('date', '>=', $today)
            ->max('date');

        // If no inventory configured at all
        if (!$earliestDate || !$latestDate) {
            $bookingStartDate = null;
            $bookingEndDate = null;
            $availability = [];
            $maxQty = 0;
            $totalQuantity = 0;
            $dayToursList = DayTour::where('is_active', true)->get();

            $season = $this->determineSeasonForCalendar();
            $selectedDate = $request->query('date');
            $bookingType = 'daytour';
            $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

            return view('booking.calendar', compact(
                'availability', 'selectedDate', 'totalQuantity',
                'maxQty', 'season', 'bookingType', 'dayToursList',
                'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
            ));
        }

        // Get all unique dates that have day tour inventory configured
        $inventoryDates = DayTourInventory::whereBetween('date', [$earliestDate, $latestDate])
            ->distinct()
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // If no inventory configured, show message or fallback to empty range
        if (empty($inventoryDates)) {
            $bookingStartDate = null;
            $bookingEndDate = null;
            $availability = [];
            $maxQty = 0;
            $totalQuantity = 0;
            $dayToursList = DayTour::where('is_active', true)->get();

            $season = $this->determineSeasonForCalendar();
            $selectedDate = $request->query('date');
            $bookingType = 'daytour';
            $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

            return view('booking.calendar', compact(
                'availability', 'selectedDate', 'totalQuantity',
                'maxQty', 'season', 'bookingType', 'dayToursList',
                'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
            ));
        }

        // Get min and max dates for calendar navigation
        $bookingStartDate = min($inventoryDates);
        $bookingEndDate = max($inventoryDates);

        // Get all active day tours
        $dayTours = DayTour::where('is_active', true)->get();

        // Build availability ONLY for dates that have inventory records
        $availability = [];
        $maxQty = 0;
        $totalQuantity = 0;

        foreach ($inventoryDates as $dateStr) {
            $d = Carbon::parse($dateStr);

            // Skip past dates
            if ($d->lt(Carbon::today())) {
                $availability[$dateStr] = [
                    'is_available' => false,
                    'min_quantity' => 0,
                    'packages' => []
                ];
                continue;
            }

            // Get total available groups across all day tours for this date
            $totalAvailable = DayTourInventory::where('date', $dateStr)
                ->where('is_blocked', false)
                ->sum('available_groups');

            // Get per-package availability
            $packages = [];
            foreach ($dayTours as $tour) {
                $inventory = DayTourInventory::where('day_tour_id', $tour->id)
                    ->where('date', $dateStr)
                    ->first();

                $availableGroups = $inventory ? $inventory->available_groups : 0;

                $packages[] = [
                    'id' => $tour->id,
                    'name' => $tour->name,
                    'price' => $tour->price,
                    'available_groups' => $availableGroups,
                    'is_available' => $availableGroups > 0 && !$inventory?->is_blocked
                ];
            }

            $availability[$dateStr] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable,
                'packages' => $packages
            ];

            // Track max for threshold calculation
            if ($totalAvailable > $maxQty) {
                $maxQty = $totalAvailable;
            }
        }

        // Use max from inventory if available, otherwise use base total
        if ($maxQty === 0) {
            $maxQty = $dayTours->sum('max_groups_per_day');
        }

        // For day tours, totalQuantity should be the max available per day (same as maxQty)
        // NOT the cumulative sum across all dates
        $totalQuantity = $maxQty;

        // Determine season for pricing
        $season = $this->determineSeasonForCalendar();

        // Single date selection for day tours
        $selectedDate = $request->query('date');

        $bookingType = 'daytour';
        $dayToursList = $dayTours;
        $maxPetsPerRoom = BookingSetting::getValue('max_pets_per_room', 2);

        return view('booking.calendar', compact(
            'availability', 'selectedDate', 'totalQuantity',
            'maxQty', 'season', 'bookingType', 'dayToursList',
            'bookingStartDate', 'bookingEndDate', 'maxPetsPerRoom'
        ));
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
