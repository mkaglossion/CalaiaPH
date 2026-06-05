<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\PaymongoWebhookEvent;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

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
            if ($request->booking_type === 'staycation') {
                $bookingQuery->whereIn('booking_type', Booking::STAYCATION_TYPES);
            } else {
                $bookingQuery->where('booking_type', $request->booking_type);
            }
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
            
            // Extract food and activity data for display
            $bookingData = $b->booking_data ?? [];
            $b->food_items = [];
            $b->activity_items = [];
            $b->pet_fee = 0;
            $b->additional_adult_fee = 0;
            $b->num_pets = 0;
            $b->has_pets = false;
            $b->additional_adults = 0;
            
            // Extract selected foods
            if (!empty($bookingData['selected_foods'])) {
                foreach ($bookingData['selected_foods'] as $food) {
                    $b->food_items[] = [
                        'name' => $food['name'] ?? 'Unknown',
                        'quantity' => (int)($food['quantity'] ?? 1),
                        'price' => (float)($food['price'] ?? 0)
                    ];
                }
            }
            
            // Extract selected activities
            if (!empty($bookingData['selected_activities'])) {
                foreach ($bookingData['selected_activities'] as $activity) {
                    $b->activity_items[] = [
                        'name' => $activity['name'] ?? 'Unknown',
                        'quantity' => (int)($activity['guests'] ?? $activity['quantity'] ?? 1),
                        'price' => (float)($activity['price'] ?? 0)
                    ];
                }
            }
            
            // Extract selected activity packages
            if (!empty($bookingData['selected_activity_packages'])) {
                foreach ($bookingData['selected_activity_packages'] as $package) {
                    $b->activity_items[] = [
                        'name' => $package['name'] ?? 'Unknown Package',
                        'quantity' => (int)($package['quantity'] ?? 1),
                        'price' => (float)($package['price'] ?? 0)
                    ];
                }
            }
            
            // Extract pet information and fees
            if (!empty($bookingData['num_pets'])) {
                $b->num_pets = (int)($bookingData['num_pets'] ?? 0);
            }
            if (!empty($bookingData['has_pets'])) {
                $b->has_pets = (bool)($bookingData['has_pets'] ?? false);
            }
            if (!empty($bookingData['pet_price'])) {
                $b->pet_fee = (float)($bookingData['pet_price'] ?? 0);
            }
            
            // Extract additional adult information and fees
            if (!empty($bookingData['additional_adults'])) {
                $b->additional_adults = (int)($bookingData['additional_adults'] ?? 0);
            }
            if (!empty($bookingData['additional_adult_price'])) {
                $b->additional_adult_fee = (float)($bookingData['additional_adult_price'] ?? 0);
            }
            // Also check for mattress_price which might be used for additional adults
            if (!empty($bookingData['mattress_price'])) {
                $b->additional_adult_fee = (float)($bookingData['mattress_price'] ?? 0);
            }
            
            return $b;
        });

        // Build PackageBooking query with equivalent filters
        $packageQuery = PackageBooking::query();

        if ($request->filled('status')) {
            $packageQuery->where('status', $request->status);
        }

        // Filter by booking type
        if ($request->filled('booking_type')) {
            if ($request->booking_type === 'package') {
                // Show only PackageBooking
                $bookingQuery->whereRaw('1 = 0'); // Exclude all Booking
            } else {
                // Show only specific Booking type (staycation, dayvisit), exclude PackageBooking
                $packageQuery->whereRaw('1 = 0'); // Exclude all PackageBooking
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
            
            // Extract food and activity data for package bookings if available
            $obj->food_items = [];
            $obj->activity_items = [];
            $obj->pet_fee = 0;
            $obj->additional_adult_fee = 0;
            $obj->num_pets = 0;
            $obj->has_pets = false;
            $obj->additional_adults = 0;
            
            // Try to get booking_data if it exists (some package bookings might have it)
            $bookingData = $pb->booking_data ?? [];
            if (!empty($bookingData)) {
                // Extract selected foods
                if (!empty($bookingData['selected_foods'])) {
                    foreach ($bookingData['selected_foods'] as $food) {
                        $obj->food_items[] = [
                            'name' => $food['name'] ?? 'Unknown',
                            'quantity' => (int)($food['quantity'] ?? 1),
                            'price' => (float)($food['price'] ?? 0)
                        ];
                    }
                }
                
                // Extract selected activities
                if (!empty($bookingData['selected_activities'])) {
                    foreach ($bookingData['selected_activities'] as $activity) {
                        $obj->activity_items[] = [
                            'name' => $activity['name'] ?? 'Unknown',
                            'quantity' => (int)($activity['guests'] ?? $activity['quantity'] ?? 1),
                            'price' => (float)($activity['price'] ?? 0)
                        ];
                    }
                }
                
                // Extract selected activity packages
                if (!empty($bookingData['selected_activity_packages'])) {
                    foreach ($bookingData['selected_activity_packages'] as $package) {
                        $obj->activity_items[] = [
                            'name' => $package['name'] ?? 'Unknown Package',
                            'quantity' => (int)($package['quantity'] ?? 1),
                            'price' => (float)($package['price'] ?? 0)
                        ];
                    }
                }
                
                // Extract pet information and fees
                if (!empty($bookingData['num_pets'])) {
                    $obj->num_pets = (int)($bookingData['num_pets'] ?? 0);
                }
                if (!empty($bookingData['has_pets'])) {
                    $obj->has_pets = (bool)($bookingData['has_pets'] ?? false);
                }
                if (!empty($bookingData['pet_price'])) {
                    $obj->pet_fee = (float)($bookingData['pet_price'] ?? 0);
                }
                
                // Extract additional adult information and fees
                if (!empty($bookingData['additional_adults'])) {
                    $obj->additional_adults = (int)($bookingData['additional_adults'] ?? 0);
                }
                if (!empty($bookingData['additional_adult_price'])) {
                    $obj->additional_adult_fee = (float)($bookingData['additional_adult_price'] ?? 0);
                }
                // Also check for mattress_price which might be used for additional adults
                if (!empty($bookingData['mattress_price'])) {
                    $obj->additional_adult_fee = (float)($bookingData['mattress_price'] ?? 0);
                }
            }
            
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
        
        // Get stats - count BOTH Booking and PackageBooking models
        $bookingConfirmed = Booking::where('status', 'confirmed')->count();
        $packageConfirmed = PackageBooking::where('status', 'confirmed')->count();
        $confirmed = $bookingConfirmed + $packageConfirmed;
        
        $bookingCancelled = Booking::where('status', 'cancelled')->count();
        $packageCancelled = PackageBooking::where('status', 'cancelled')->count();
        $cancelled = $bookingCancelled + $packageCancelled;
        
        $bookingCompleted = Booking::where('status', 'completed')->count();
        $packageCompleted = PackageBooking::where('status', 'completed')->count();
        $completed = $bookingCompleted + $packageCompleted;
        
        $totalBookings = Booking::count();
        $totalPackages = PackageBooking::count();
        $total = $totalBookings + $totalPackages;
        
        $todayBookings = Booking::whereDate('created_at', Carbon::today())->count();
        $todayPackages = PackageBooking::whereDate('created_at', Carbon::today())->count();
        $today = $todayBookings + $todayPackages;
        
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
        $oldStatus = $booking->status;
        $booking->cancel();
        AdminActivityLog::log(
            'booking_status_update',
            "Cancelled booking {$booking->transaction_id} from {$oldStatus} to cancelled",
            $booking,
            ['old_status' => $oldStatus, 'new_status' => 'cancelled']
        );
        
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

    public function paymongoWebhooks(Request $request)
    {
        if (!Schema::hasTable('paymongo_webhook_events')) {
            $events = new LengthAwarePaginator(
                collect(),
                0,
                20,
                max(1, (int) $request->query('page', 1)),
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $stats = [
                'total' => 0,
                'ok' => 0,
                'failed' => 0,
                'handled' => 0,
                'invalid_signature' => 0,
            ];
            $tableReady = false;

            return view('admin.bookings.paymongo-webhooks', compact('events', 'stats', 'tableReady'));
        }

        $query = PaymongoWebhookEvent::query()->latest();

        if ($request->filled('event_type')) {
            $eventType = (string) $request->string('event_type');
            $query->where('event_type', 'like', '%' . $eventType . '%');
        }

        if ($request->filled('reference')) {
            $query->where(function ($q) use ($request) {
                $value = (string) $request->string('reference');
                $q->where('reference', 'like', '%' . $value . '%')
                    ->orWhere('checkout_session_id', 'like', '%' . $value . '%')
                    ->orWhere('payment_id', 'like', '%' . $value . '%');
            });
        }

        if ($request->filled('signature_valid')) {
            $signatureValid = $request->input('signature_valid');
            if (in_array($signatureValid, ['0', '1'], true)) {
                $query->where('signature_valid', (int) $signatureValid);
            }
        }

        if ($request->filled('handled')) {
            $handled = $request->input('handled');
            if (in_array($handled, ['0', '1'], true)) {
                $query->where('handled', (int) $handled);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $events = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => PaymongoWebhookEvent::count(),
            'ok' => PaymongoWebhookEvent::where('http_status', 200)->count(),
            'failed' => PaymongoWebhookEvent::where('http_status', '!=', 200)->count(),
            'handled' => PaymongoWebhookEvent::where('handled', true)->count(),
            'invalid_signature' => PaymongoWebhookEvent::where('signature_present', true)->where('signature_valid', false)->count(),
        ];
        $tableReady = true;

        return view('admin.bookings.paymongo-webhooks', compact('events', 'stats', 'tableReady'));
    }
}
