<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTour;
use App\Models\Package;
use App\Models\Staycation;
use Carbon\Carbon;

class InventoryManagementController extends Controller
{
    /**
     * Display unified inventory management dashboard
     */
    public function index()
    {
        $staycations = Staycation::with(['inventories' => function ($query) {
            $query->where('date', '>=', Carbon::today())->orderBy('date');
        }])->get();
        
        $packages = Package::with(['inventory' => function ($query) {
            $query->where('date', '>=', Carbon::today())->orderBy('date');
        }])->get();
        
        $dayTours = DayTour::with(['inventory' => function ($query) {
            $query->where('date', '>=', Carbon::today())->orderBy('date');
        }])->where('is_active', true)->get();

        // Pagination settings
        $itemsPerPage = 10;

        return view('admin.inventory.unified-management', compact('staycations', 'packages', 'dayTours', 'itemsPerPage'));
    }
}
