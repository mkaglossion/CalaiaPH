<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminBookingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by booking type
        if ($request->filled('booking_type')) {
            $query->where('booking_type', $request->booking_type);
        }
        
        // Search by customer name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('checkin_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('checkin_date', '<=', $request->date_to);
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        // Get stats
        $stats = [
            'total' => Booking::count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'today' => Booking::whereDate('created_at', Carbon::today())->count(),
        ];
        
        return view('admin.bookings.index', compact('bookings', 'stats'));
    }
    
    public function show($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Load staycation names for accommodations
        $staycations = Staycation::all()->keyBy('id');
        
        return view('admin.bookings.show', compact('booking', 'staycations'));
    }
    
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->cancel();
        
        return redirect()->back()->with('success', 'Booking cancelled and inventory restored successfully.');
    }
    
    public function inventory(Request $request)
    {
        $dateFrom = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : Carbon::today();
        
        $dateTo = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : Carbon::today()->addDays(30);
        
        // Build full date list in range for pagination
        $dateRange = collect(CarbonPeriod::create($dateFrom, $dateTo))
            ->map(fn(Carbon $d): string => $d->toDateString())
            ->values();
        
        $perPage = 15;
        $page = max(1, (int) $request->query('page', 1));
        $totalDays = $dateRange->count();
        $sliceDates = $dateRange->slice(($page - 1) * $perPage, $perPage)->values();
        
        $pagination = new LengthAwarePaginator(
            $sliceDates,
            $totalDays,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $staycations = Staycation::all();
        
        $inventoryData = [];
        $dailyTotals = [];
        
        foreach ($staycations as $staycation) {
            $inventories = StaycationInventory::where('staycation_id', $staycation->id)
                ->whereIn('date', $sliceDates)
                ->orderBy('date')
                ->get();
            
            $inventoryData[$staycation->id] = [
                'staycation' => $staycation,
                'inventories' => $inventories,
            ];

            // Aggregate daily totals across all staycations so admins can see overall availability per date
            foreach ($inventories as $inventory) {
                // Normalize date to string key to avoid object offsets
                $dateKey = Carbon::parse($inventory->date)->toDateString();

                if (!isset($dailyTotals[$dateKey])) {
                    $dailyTotals[$dateKey] = [
                        'date' => $dateKey,
                        'total_quantity' => 0,
                        'booked_quantity' => 0,
                        'available_quantity' => 0,
                    ];
                }

                $dailyTotals[$dateKey]['total_quantity'] += $inventory->total_quantity;
                $dailyTotals[$dateKey]['booked_quantity'] += $inventory->booked_quantity;
                $dailyTotals[$dateKey]['available_quantity'] += $inventory->available_quantity;
            }
        }

        // Sort daily totals by date ascending
        ksort($dailyTotals);
        
        return view('admin.bookings.inventory', compact('inventoryData', 'dailyTotals', 'dateFrom', 'dateTo', 'staycations', 'pagination', 'perPage', 'totalDays'));
    }
}
