<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PackageBooking;
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
        // Build Booking query with filters
        $bookingQuery = Booking::query();

        if ($request->filled('status')) {
            $bookingQuery->where('status', $request->status);
        }

        if ($request->filled('booking_type')) {
            $bookingQuery->where('booking_type', $request->booking_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $bookingQuery->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $bookingQuery->where('checkin_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $bookingQuery->where('checkin_date', '<=', $request->date_to);
        }

        $bookingsCollection = $bookingQuery->orderBy('created_at', 'desc')->get()->map(function($b) {
            $b->resource_type = 'booking';
            $b->resource_id = $b->id;
            $b->display_type_label = ucfirst($b->booking_type ?? 'staycation');
            $b->display_customer_name = $b->customer_name;
            return $b;
        });

        // Build PackageBooking query with equivalent filters
        $packageQuery = PackageBooking::query();

        if ($request->filled('status')) {
            $packageQuery->where('status', $request->status);
        }

        // Map booking_type filter to package if requested
        if ($request->filled('booking_type') && $request->booking_type !== 'package') {
            // no-op, package bookings only match when booking_type is 'package'
            if ($request->booking_type !== 'package') {
                $packageQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $packageQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('booking_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $packageQuery->where('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $packageQuery->where('booking_date', '<=', $request->date_to);
        }

        $packageCollection = $packageQuery->orderBy('created_at', 'desc')->get()->map(function($pb) {
            $obj = new \stdClass();
            $obj->id = $pb->id; // preserve for direct use if needed
            $obj->resource_type = 'package';
            $obj->resource_id = $pb->id;
            $obj->transaction_id = $pb->booking_reference;
            $obj->created_at = $pb->created_at;
            $obj->display_customer_name = $pb->full_name;
            $obj->customer_email = $pb->email;
            $obj->customer_phone = $pb->phone ?? null;
            $obj->booking_type = 'package';
            $obj->display_type_label = 'Package';
            $obj->checkin_date = $pb->booking_date;
            $obj->checkout_date = null;
            $obj->total_amount = $pb->total_amount;
            $obj->status = $pb->status;
            $obj->payment_status = $pb->payment_status ?? 'pending';
            $obj->id_for_routes = $pb->id; // id specifically for package routes
            return $obj;
        });

        // Merge both collections and sort by created_at desc
        $merged = $bookingsCollection->concat($packageCollection)->sortByDesc(function($item) {
            return $item->created_at;
        })->values();

        // Manual pagination
        $perPage = 10;
        $page = max(1, (int) $request->query('page', 1));
        $total = $merged->count();
        $slice = $merged->slice(($page - 1) * $perPage, $perPage)->values();
        $bookings = new LengthAwarePaginator($slice, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        
        // Get stats
        $confirmed = Booking::where('status', 'confirmed')->count();
        $cancelled = Booking::where('status', 'cancelled')->count();
        $completed = Booking::where('status', 'completed')->count();
        $total = Booking::count();
        $today = Booking::whereDate('created_at', Carbon::today())->count();
        
        $stats = [
            'total' => $total,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'completed' => $completed,
            'today' => $today,
        ];
        
        // Stats for stat cards with subtitles
        $statCardsData = [
            ['label' => 'Total Bookings', 'value' => $total, 'subtitle' => ''],
            ['label' => 'Confirmed', 'value' => $confirmed, 'subtitle' => 'Confirmed: ' . $confirmed],
            ['label' => 'Completed', 'value' => $completed, 'subtitle' => 'Completed: ' . $completed],
            ['label' => 'Cancelled', 'value' => $cancelled, 'subtitle' => 'Cancelled: ' . $cancelled],
            ['label' => 'Today', 'value' => $today, 'subtitle' => ''],
        ];
        
        return view('admin.bookings.index', compact('bookings', 'stats', 'statCardsData'));
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
            : Carbon::today()->addDays(365);
        
        // Get only dates that have inventory configured (like the booking calendar does)
        $dateRange = StaycationInventory::whereBetween('date', [$dateFrom, $dateTo])
            ->distinct()
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values();
        
        // If no inventory exists, return empty view
        if ($dateRange->isEmpty()) {
            $inventoryData = [];
            $dailyTotals = [];
            $pagination = new LengthAwarePaginator(
                [],
                0,
                15,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $staycations = Staycation::all();
            $perPage = 15;
            $totalDays = 0;
            return view('admin.bookings.inventory', compact('inventoryData', 'dailyTotals', 'dateFrom', 'dateTo', 'staycations', 'pagination', 'perPage', 'totalDays'));
        }
        
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
