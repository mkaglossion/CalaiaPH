<?php

namespace App\Http\Controllers;

use App\Models\HomeSwiperImage;
use App\Models\HomeText;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Models\DayTour;
use App\Models\DayTourInventory;

class HomeController extends Controller
{
    public function index()
    {
        $swiperImages = HomeSwiperImage::all();
        $texts = HomeText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();

        // Build availability from staycation inventory
        $availability = [];
        $start = Carbon::today();
        $end = (clone $start)->addMonths(6);

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
            $totalAvailable = StaycationInventory::where('date', $key)
                ->sum('available_quantity');

            // If no inventory records exist for this date, fall back to staycation quantity
            if ($totalAvailable == 0) {
                $totalAvailable = Staycation::sum('quantity');
            }

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];
        }

        // Get total quantity from staycations for legend
        $totalQuantity = Staycation::sum('quantity');

        // Build day tour availability data
        $dayTourAvailability = [];
        $dayTours = DayTour::where('is_active', true)->get();
        $totalDayTourGroups = $dayTours->sum('max_groups_per_day');

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(Carbon::today())) {
                $dayTourAvailability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available groups for this date from day tour inventory
            $totalAvailableGroups = DayTourInventory::where('date', $key)
                ->where('is_blocked', false)
                ->sum('available_groups');

            // If no inventory records exist for this date, fall back to sum of max_groups_per_day
            if ($totalAvailableGroups == 0) {
                $totalAvailableGroups = $totalDayTourGroups;
            }

            $dayTourAvailability[$key] = [
                'is_available' => $totalAvailableGroups > 0,
                'min_quantity' => $totalAvailableGroups
            ];
        }

        // Get total day tour groups for legend
        $totalDayTourQuantity = $totalDayTourGroups;

        return view('home', compact('swiperImages', 'texts', 'availability', 'totalQuantity', 'dayTourAvailability', 'totalDayTourQuantity'));
    }
}
