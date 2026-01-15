<?php

namespace App\Http\Controllers;

use App\Models\HomeSwiperImage;
use App\Models\HomeText;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Staycation;
use App\Models\StaycationInventory;

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

        return view('home', compact('swiperImages', 'texts', 'availability', 'totalQuantity'));
    }
}