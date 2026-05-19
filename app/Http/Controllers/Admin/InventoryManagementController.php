<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTour;
use App\Models\Package;
use App\Models\Staycation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryManagementController extends Controller
{
    /**
     * Display unified inventory management dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->addDays(30)->endOfDay();

        $staycations = Staycation::with(['inventories' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->get();
        
        $packages = Package::with(['inventory' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->get();
        
        $dayTours = DayTour::with(['inventory' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->where('is_active', true)->get();

        // Pagination settings
        $itemsPerPage = 10;

        return view('admin.inventory.unified-management', compact('staycations', 'packages', 'dayTours', 'itemsPerPage', 'dateFrom', 'dateTo'));
    }
}
