<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\DayTour;
use App\Models\Food;
use App\Models\PackageBooking;
use App\Models\PaymongoWebhookEvent;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Services\PaymentService;
use App\Services\PaymongoWebhookService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminBookingsController extends Controller
{
    public function index(Request $request)
    {
        $pendingCutoff = $this->pendingCutoff();
        $this->expireStalePendingBookings($pendingCutoff, app(PaymentService::class));

        // Build Booking query with filters
        $bookingQuery = Booking::query();
        $paymentScope = $this->normalizePaymentScope($request);
        if ($request->filled('status') && !$request->filled('payment_status') && !$request->filled('payment_scope')) {
            $paymentScope = 'all';
        }

        $this->applyPaymentLifecycleFilters($bookingQuery, $request, $paymentScope, $pendingCutoff);

        if ($request->filled('status')) {
            $bookingQuery->where('status', $request->status);
        }

        if ($request->boolean('exclude_cancelled')) {
            $bookingQuery->where('status', '!=', 'cancelled');
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

        $bookingDateColumn = $this->usesPaymentAttemptDate($request, $paymentScope)
            ? 'created_at'
            : 'checkin_date';
        $this->applyDateRangeFilter($bookingQuery, $request, $bookingDateColumn);

        $bookingsCollection = $bookingQuery->orderBy('created_at', 'desc')->get()->map(function($b) use ($pendingCutoff) {
            $b->resource_type = 'booking';
            $b->resource_id = $b->id;
            
            // Extract food and activity data for display
            $bookingData = $b->booking_data ?? [];
            $accommodationSummary = $this->summarizeStaycationAccommodations($bookingData);
            $foodItems = [];
            $activityItems = [];
            $petFee = 0;
            $additionalAdultFee = 0;
            $numPets = 0;
            $hasPets = false;
            $additionalAdults = 0;
            
            // Extract selected foods
            if (!empty($bookingData['selected_foods'])) {
                foreach ($bookingData['selected_foods'] as $food) {
                    $foodItems[] = [
                        'name' => $food['name'] ?? 'Unknown',
                        'quantity' => (int)($food['quantity'] ?? 1),
                        'price' => (float)($food['price'] ?? 0)
                    ];
                }
            }
            
            // Extract selected activities
            if (!empty($bookingData['selected_activities'])) {
                foreach ($bookingData['selected_activities'] as $activity) {
                    $activityItems[] = [
                        'name' => $activity['name'] ?? 'Unknown',
                        'quantity' => (int)($activity['guests'] ?? $activity['quantity'] ?? 1),
                        'price' => (float)($activity['price'] ?? 0)
                    ];
                }
            }
            
            // Extract selected activity packages
            if (!empty($bookingData['selected_activity_packages'])) {
                foreach ($bookingData['selected_activity_packages'] as $package) {
                    $activityItems[] = [
                        'name' => $package['name'] ?? 'Unknown Package',
                        'quantity' => (int)($package['quantity'] ?? 1),
                        'price' => (float)($package['price'] ?? 0)
                    ];
                }
            }
            
            // Extract pet information and fees
            if (!empty($bookingData['num_pets'])) {
                $numPets = (int)($bookingData['num_pets'] ?? 0);
            }
            if (!empty($bookingData['has_pets'])) {
                $hasPets = (bool)($bookingData['has_pets'] ?? false);
            }
            if (!empty($bookingData['pet_price'])) {
                $petFee = (float)($bookingData['pet_price'] ?? 0);
            }
            
            // Extract additional adult information and fees
            if (!empty($bookingData['additional_adults'])) {
                $additionalAdults = (int)($bookingData['additional_adults'] ?? 0);
            }
            if (!empty($bookingData['additional_adult_price'])) {
                $additionalAdultFee = (float)($bookingData['additional_adult_price'] ?? 0);
            }
            // Also check for mattress_price which might be used for additional adults
            if (!empty($bookingData['mattress_price'])) {
                $additionalAdultFee = (float)($bookingData['mattress_price'] ?? 0);
            }
            
            // Set attributes
            $b->setAttribute('food_items', $foodItems);
            $b->setAttribute('activity_items', $activityItems);
            $b->setAttribute('pet_fee', $petFee);
            $b->setAttribute('additional_adult_fee', $additionalAdultFee);
            $b->setAttribute('num_pets', $numPets);
            $b->setAttribute('has_pets', $hasPets);
            $b->setAttribute('additional_adults', $additionalAdults);
            $b->setAttribute('accommodation_summary', $accommodationSummary['label']);
            $b->setAttribute('accommodation_unit_count', $accommodationSummary['unit_count']);
            $b->setAttribute('accommodation_guest_count', $accommodationSummary['guest_count']);
            
            return $this->applyDerivedPaymentLifecycleState($b, $pendingCutoff);
        });

        // Build PackageBooking query with equivalent filters
        $packageQuery = PackageBooking::query();
        $this->applyPaymentLifecycleFilters($packageQuery, $request, $paymentScope, $pendingCutoff);

        if ($request->filled('status')) {
            $packageQuery->where('status', $request->status);
        }

        if ($request->boolean('exclude_cancelled')) {
            $packageQuery->where('status', '!=', 'cancelled');
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

        $packageDateColumn = $this->usesPaymentAttemptDate($request, $paymentScope)
            ? 'created_at'
            : 'booking_date';
        $this->applyDateRangeFilter($packageQuery, $request, $packageDateColumn);

        $packageCollection = $packageQuery->orderBy('created_at', 'desc')->get()->map(function($pb) use ($pendingCutoff) {
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
            
            return $this->applyDerivedPaymentLifecycleState($obj, $pendingCutoff);
        });

        // Merge both collections and sort by created_at desc
        $merged = $bookingsCollection->concat($packageCollection)->sortByDesc(function($item) {
            return $item->created_at;
        })->values();

        // Manual pagination
        $perPage = 10;
        $total = $merged->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $slice = $merged->slice(($page - 1) * $perPage, $perPage)->values();
        $bookings = new LengthAwarePaginator($slice, $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        
        $confirmed = $merged->where('status', 'confirmed')->count();
        $active = $confirmed;
        $cancelled = $merged->where('status', 'cancelled')->count();
        $completed = $merged->where('status', 'completed')->count();
        $pending = $merged->filter(fn ($item) => strtolower((string) ($item->effective_payment_status ?? $item->payment_status ?? '')) === 'pending')->count();
        $total = $merged->count();
        $today = $merged->filter(fn ($item) => $item->created_at instanceof Carbon && $item->created_at->isToday())->count();
        
        $stats = [
            'total' => $total,
            'active' => $active,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'completed' => $completed,
            'pending' => $pending,
            'today' => $today,
        ];
        
        // Stats for stat cards with subtitles
        $statCardsData = [
            [
                'label' => 'Active Bookings',
                'value' => $active,
                'subtitle' => 'Confirmed operational bookings',
                'icon' => 'calendar-check-2',
                'iconBg' => 'bg-blue-50',
                'iconClass' => 'text-blue-600',
                'href' => route('admin.bookings.index', ['status' => 'confirmed']),
            ],
            [
                'label' => 'Completed',
                'value' => $completed,
                'subtitle' => 'Checked out bookings',
                'icon' => 'check-circle-2',
                'iconBg' => 'bg-emerald-50',
                'iconClass' => 'text-emerald-600',
                'href' => route('admin.bookings.index', ['status' => 'completed']),
            ],
            [
                'label' => 'Cancelled',
                'value' => $cancelled,
                'subtitle' => 'Voided reservations',
                'icon' => 'ban',
                'iconBg' => 'bg-rose-50',
                'iconClass' => 'text-rose-600',
                'href' => route('admin.bookings.index', ['status' => 'cancelled']),
            ],
            [
                'label' => 'Today',
                'value' => $today,
                'subtitle' => 'Created today',
                'icon' => 'sun',
                'iconBg' => 'bg-amber-50',
                'iconClass' => 'text-amber-600',
                'href' => route('admin.bookings.index', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]),
            ],
        ];
        
        return view('admin.bookings.index', compact('bookings', 'stats', 'statCardsData', 'paymentScope'));
    }

    public function export(Request $request)
    {
        $pendingCutoff = $this->pendingCutoff();
        $this->expireStalePendingBookings($pendingCutoff, app(PaymentService::class));

        $paymentScope = $this->normalizePaymentScope($request);
        if ($request->filled('status') && !$request->filled('payment_status')) {
            $paymentScope = 'all';
        }
        $merged = $this->buildMergedBookingsCollection($request, $paymentScope, $pendingCutoff);

        $generatedAt = now()->format('M d, Y H:i:s');
        $filename = 'bookings-export-' . now()->format('Y-m-d-His') . '.xls';

        $rows = $merged->map(function ($item) {
            $checkin = $item->checkin_date instanceof Carbon
                ? $item->checkin_date->format('M d, Y')
                : ($item->checkin_date ? Carbon::parse($item->checkin_date)->format('M d, Y') : '');

            $checkout = $item->checkout_date instanceof Carbon
                ? $item->checkout_date->format('M d, Y')
                : ($item->checkout_date ? Carbon::parse($item->checkout_date)->format('M d, Y') : 'N/A');

            return [
                'Type' => $item->display_type_label ?? ucfirst((string) ($item->booking_type ?? $item->resource_type ?? 'Booking')),
                'Customer' => $item->display_customer_name ?? $item->customer_name ?? $item->full_name ?? '',
                'Email' => $item->customer_email ?? $item->email ?? '',
                'Phone' => $item->customer_phone ?? $item->phone ?? '',
                'Reference' => $item->transaction_id ?? $item->booking_reference ?? '',
                'Check-in' => $checkin,
                'Check-out' => $checkout,
                'Status' => $item->status ?? '',
                'Payment' => $item->effective_payment_status ?? $item->payment_status ?? '',
                'Amount' => (float) ($item->total_amount ?? 0),
                'Created At' => optional($item->created_at)->format('M d, Y H:i'),
            ];
        })->values()->all();

        $summaryRows = [
            ['Metric' => 'Filtered Records', 'Value' => $merged->count()],
            ['Metric' => 'Confirmed', 'Value' => $merged->where('status', 'confirmed')->count()],
            ['Metric' => 'Completed', 'Value' => $merged->where('status', 'completed')->count()],
            ['Metric' => 'Cancelled', 'Value' => $merged->where('status', 'cancelled')->count()],
            ['Metric' => 'Paid', 'Value' => $merged->filter(fn ($item) => ($item->effective_payment_status ?? $item->payment_status ?? '') === 'paid')->count()],
            ['Metric' => 'Pending', 'Value' => $merged->filter(fn ($item) => ($item->effective_payment_status ?? $item->payment_status ?? '') === 'pending')->count()],
            ['Metric' => 'Total Amount', 'Value' => (float) $merged->sum(fn ($item) => (float) ($item->total_amount ?? 0))],
        ];

        $html = $this->renderExcelWorkbook([
            [
                'title' => 'Summary',
                'headers' => ['Metric', 'Value'],
                'rows' => $summaryRows,
            ],
            [
                'title' => 'Bookings',
                'headers' => ['Type', 'Customer', 'Email', 'Phone', 'Reference', 'Check-in', 'Check-out', 'Status', 'Payment', 'Amount', 'Created At'],
                'rows' => $rows,
            ],
        ], 'Filtered bookings', $generatedAt);

        return response($html, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function show(int $id)
    {
        $booking = Booking::with(['bookingGuests.signer'])->findOrFail($id);

        if (($booking->payment_method ?? null) === 'paymongo'
            && in_array(($booking->payment_status ?? null), ['pending', 'failed'], true)
            && !empty($booking->paymongo_checkout_id)) {
            $paymentStatus = app(PaymentService::class)->getCheckoutPaymentStatus($booking->paymongo_checkout_id);

            if (($paymentStatus['status'] ?? null) === 'paid') {
                app(PaymentService::class)->confirmPendingBooking($booking, [
                    'checkout_id' => $booking->paymongo_checkout_id,
                    'payment_reference' => $paymentStatus['payment_reference'] ?? null,
                    'paid_amount_centavos' => $paymentStatus['paid_amount_centavos'] ?? null,
                ]);
                $booking = Booking::with(['bookingGuests.signer'])->findOrFail($id);
            }
        }

        $staycations = Staycation::with('swiperImages')->get()->keyBy('id');
        $bookingData = is_array($booking->booking_data) ? $booking->booking_data : [];

        $selectedPackages = $this->extractSelectedPackages($bookingData);
        $foodItems = $this->extractFoodItems($bookingData);
        $singleActivities = $this->extractSelectedActivities($bookingData);
        $activityPackages = $this->extractSelectedActivityPackages($bookingData);

        $foodIds = collect($foodItems)->pluck('id')->filter()->unique()->values()->all();
        $activityIds = collect($singleActivities)->pluck('id')->filter()->unique()->values()->all();
        $activityPackageIds = collect($activityPackages)->pluck('package_id')->filter()->unique()->values()->all();
        $dayTourPackageIds = collect($selectedPackages)->pluck('package_id')->filter()->unique()->values()->all();

        $foodImages = !empty($foodIds) ? Food::whereIn('id', $foodIds)->pluck('image', 'id')->all() : [];
        $activityImages = !empty($activityIds) ? Activity::whereIn('id', $activityIds)->pluck('image', 'id')->all() : [];
        $activityPackageImages = !empty($activityPackageIds) ? \App\Models\ActivityPackage::whereIn('id', $activityPackageIds)->pluck('image', 'id')->all() : [];
        $dayTourImages = !empty($dayTourPackageIds) ? DayTour::whereIn('id', $dayTourPackageIds)->pluck('image', 'id')->all() : [];

        return view('admin.bookings.show', compact(
            'booking',
            'staycations',
            'selectedPackages',
            'foodItems',
            'singleActivities',
            'activityPackages',
            'foodImages',
            'activityImages',
            'activityPackageImages',
            'dayTourImages'
        ));
    }

    public function guestSignature(int $bookingId, int $guestId)
    {
        $booking = Booking::findOrFail($bookingId);
        abort_unless($booking->isStaycationFamilyBooking(), 404);

        $guest = BookingGuest::where('booking_id', $booking->id)->whereKey($guestId)->firstOrFail();
        abort_unless(!empty($guest->signature_path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($guest->signature_path), 404);

        return $disk->response($guest->signature_path, basename($guest->signature_path), [
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
    
    public function cancel(int $id)
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
        
        $staycationIds = $staycations->pluck('id')->all();
        
        $bookingsQuery = Booking::query()
            ->whereIn('booking_type', Booking::STAYCATION_TYPES)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereIn('checkin_date', $sliceDates)
            ->get();
        
        $packagesBookedByDateStaycation = [];
        $packagesBookedByDate = [];
        
        foreach ($bookingsQuery as $booking) {
            $checkinDate = $booking->checkin_date instanceof Carbon
                ? $booking->checkin_date->toDateString()
                : Carbon::parse($booking->checkin_date)->toDateString();
            
            $bookingData = $booking->booking_data ?? [];
            $accommodations = $bookingData['accommodations'] ?? [];
            
            foreach ($accommodations as $accommodation) {
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                if ($staycationId) {
                    $dateKey = $checkinDate;
                    $packagesBookedByDateStaycation[$staycationId][$dateKey] = ($packagesBookedByDateStaycation[$staycationId][$dateKey] ?? 0) + 1;
                }
            }
            
            if (!isset($packagesBookedByDate[$checkinDate])) {
                $packagesBookedByDate[$checkinDate] = 0;
            }
            $packagesBookedByDate[$checkinDate] += count($accommodations);
        }
        
        foreach ($staycations as $staycation) {
            $inventories = StaycationInventory::where('staycation_id', $staycation->id)
                ->whereIn('date', $sliceDates)
                ->orderBy('date')
                ->get();
            
            $inventories = $inventories->map(function ($inventory) use ($packagesBookedByDateStaycation) {
                $dateKey = Carbon::parse($inventory->date)->toDateString();
                $packagesBooked = $packagesBookedByDateStaycation[$inventory->staycation_id][$dateKey] ?? 0;
                $inventory->packages_booked = $packagesBooked;
                return $inventory;
            });
            
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
                        'packages_booked' => 0,
                    ];
                }

                $dailyTotals[$dateKey]['total_quantity'] += $inventory->total_quantity;
                $dailyTotals[$dateKey]['booked_quantity'] += $inventory->booked_quantity;
                $dailyTotals[$dateKey]['available_quantity'] += $inventory->available_quantity;
                $dailyTotals[$dateKey]['packages_booked'] += $inventory->packages_booked ?? 0;
            }
        }

        // Sort daily totals by date ascending
        ksort($dailyTotals);
        
        return view('admin.bookings.inventory', compact('inventoryData', 'dailyTotals', 'dateFrom', 'dateTo', 'staycations', 'pagination', 'perPage', 'totalDays'));
    }

    public function paymongoWebhooks(Request $request)
    {
        $retentionDays = PaymongoWebhookEvent::retentionDays();
        $invalidRetentionDays = PaymongoWebhookEvent::invalidRetentionDays();
        $cleanupCutoff = now()->subDays($retentionDays);

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
                'cleanup_eligible' => 0,
            ];
            $tableReady = false;
            $eligibleEventIds = collect();

            return view('admin.bookings.paymongo-webhooks', compact(
                'events',
                'stats',
                'tableReady',
                'retentionDays',
                'invalidRetentionDays',
                'cleanupCutoff',
                'eligibleEventIds'
            ));
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
            'cleanup_eligible' => PaymongoWebhookEvent::eligibleForCleanup($cleanupCutoff)->count(),
        ];
        $tableReady = true;
        $eligibleEventIds = $events->getCollection()
            ->filter(fn (PaymongoWebhookEvent $event) => $event->isEligibleForCleanup($cleanupCutoff))
            ->pluck('id')
            ->values();

        return view('admin.bookings.paymongo-webhooks', compact(
            'events',
            'stats',
            'tableReady',
            'retentionDays',
            'invalidRetentionDays',
            'cleanupCutoff',
            'eligibleEventIds'
        ));
    }

    public function bulkDeletePaymongoWebhooks(Request $request)
    {
        if (!Schema::hasTable('paymongo_webhook_events')) {
            return back()->with('error', 'PayMongo webhook log table is not available.');
        }

        $validated = $request->validate([
            'event_ids' => ['required', 'array', 'min:1', 'max:100'],
            'event_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $eventIds = array_values(array_unique(array_map('intval', $validated['event_ids'])));
        $retentionDays = PaymongoWebhookEvent::retentionDays();
        $cleanupCutoff = now()->subDays($retentionDays);
        $eligibleIds = PaymongoWebhookEvent::eligibleForCleanup($cleanupCutoff)
            ->whereKey($eventIds)
            ->pluck('id');

        if ($eligibleIds->isEmpty()) {
            return back()->with('error', "No selected logs are eligible for cleanup. Invalid or missing-signature HTTP 401 requests can be deleted immediately; handled, valid HTTP 200 events require {$retentionDays} days of retention.");
        }

        $deleted = PaymongoWebhookEvent::whereKey($eligibleIds)->delete();
        $skipped = count($eventIds) - $deleted;

        AdminActivityLog::log(
            'paymongo_webhook_bulk_delete',
            "Deleted {$deleted} eligible PayMongo webhook log(s)",
            null,
            [
                'deleted_count' => $deleted,
                'skipped_count' => $skipped,
                'retention_days' => $retentionDays,
            ]
        );

        $message = "Deleted {$deleted} eligible PayMongo webhook log(s) successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} protected or missing selection(s) were skipped.";
        }

        return back()->with('success', $message);
    }

    private function buildMergedBookingsCollection(Request $request, string $paymentScope, Carbon $pendingCutoff)
    {
        $bookingQuery = Booking::query();
        $this->applyPaymentLifecycleFilters($bookingQuery, $request, $paymentScope, $pendingCutoff);

        if ($request->filled('status')) {
            $bookingQuery->where('status', $request->status);
        }

        if ($request->boolean('exclude_cancelled')) {
            $bookingQuery->where('status', '!=', 'cancelled');
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
            $bookingQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        $bookingDateColumn = $this->usesPaymentAttemptDate($request, $paymentScope)
            ? 'created_at'
            : 'checkin_date';
        $this->applyDateRangeFilter($bookingQuery, $request, $bookingDateColumn);

        $bookingsCollection = $bookingQuery->orderBy('created_at', 'desc')->get()->map(function ($b) use ($pendingCutoff) {
            $b->resource_type = 'booking';
            $b->resource_id = $b->id;

            $bookingData = $b->booking_data ?? [];
            $foodItems = [];
            $activityItems = [];
            $petFee = 0;
            $additionalAdultFee = 0;
            $numPets = 0;
            $hasPets = false;
            $additionalAdults = 0;

            if (!empty($bookingData['selected_foods'])) {
                foreach ($bookingData['selected_foods'] as $food) {
                    $foodItems[] = [
                        'name' => $food['name'] ?? 'Unknown',
                        'quantity' => (int) ($food['quantity'] ?? 1),
                        'price' => (float) ($food['price'] ?? 0),
                    ];
                }
            }

            if (!empty($bookingData['selected_activities'])) {
                foreach ($bookingData['selected_activities'] as $activity) {
                    $activityItems[] = [
                        'name' => $activity['name'] ?? 'Unknown',
                        'quantity' => (int) ($activity['guests'] ?? $activity['quantity'] ?? 1),
                        'price' => (float) ($activity['price'] ?? 0),
                    ];
                }
            }

            if (!empty($bookingData['selected_activity_packages'])) {
                foreach ($bookingData['selected_activity_packages'] as $package) {
                    $activityItems[] = [
                        'name' => $package['name'] ?? 'Unknown Package',
                        'quantity' => (int) ($package['quantity'] ?? 1),
                        'price' => (float) ($package['price'] ?? 0),
                    ];
                }
            }

            if (!empty($bookingData['num_pets'])) {
                $numPets = (int) ($bookingData['num_pets'] ?? 0);
            }
            if (!empty($bookingData['has_pets'])) {
                $hasPets = (bool) ($bookingData['has_pets'] ?? false);
            }
            if (!empty($bookingData['pet_price'])) {
                $petFee = (float) ($bookingData['pet_price'] ?? 0);
            }

            if (!empty($bookingData['additional_adults'])) {
                $additionalAdults = (int) ($bookingData['additional_adults'] ?? 0);
            }
            if (!empty($bookingData['additional_adult_price'])) {
                $additionalAdultFee = (float) ($bookingData['additional_adult_price'] ?? 0);
            }
            if (!empty($bookingData['mattress_price'])) {
                $additionalAdultFee = (float) ($bookingData['mattress_price'] ?? 0);
            }

            $b->setAttribute('food_items', $foodItems);
            $b->setAttribute('activity_items', $activityItems);
            $b->setAttribute('pet_fee', $petFee);
            $b->setAttribute('additional_adult_fee', $additionalAdultFee);
            $b->setAttribute('num_pets', $numPets);
            $b->setAttribute('has_pets', $hasPets);
            $b->setAttribute('additional_adults', $additionalAdults);

            return $this->applyDerivedPaymentLifecycleState($b, $pendingCutoff);
        });

        $packageQuery = PackageBooking::query();
        $this->applyPaymentLifecycleFilters($packageQuery, $request, $paymentScope, $pendingCutoff);

        if ($request->filled('status')) {
            $packageQuery->where('status', $request->status);
        }

        if ($request->boolean('exclude_cancelled')) {
            $packageQuery->where('status', '!=', 'cancelled');
        }

        if ($request->filled('booking_type')) {
            if ($request->booking_type === 'package') {
                $bookingQuery->whereRaw('1 = 0');
            } else {
                $packageQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $packageQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('booking_reference', 'like', "%{$search}%");
            });
        }

        $packageDateColumn = $this->usesPaymentAttemptDate($request, $paymentScope)
            ? 'created_at'
            : 'booking_date';
        $this->applyDateRangeFilter($packageQuery, $request, $packageDateColumn);

        $packageCollection = $packageQuery->orderBy('created_at', 'desc')->get()->map(function ($pb) use ($pendingCutoff) {
            $obj = new \stdClass();
            $obj->id = $pb->id;
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
            $obj->id_for_routes = $pb->id;

            $obj->food_items = [];
            $obj->activity_items = [];
            $obj->pet_fee = 0;
            $obj->additional_adult_fee = 0;
            $obj->num_pets = 0;
            $obj->has_pets = false;
            $obj->additional_adults = 0;

            $bookingData = $pb->booking_data ?? [];
            if (!empty($bookingData)) {
                if (!empty($bookingData['selected_foods'])) {
                    foreach ($bookingData['selected_foods'] as $food) {
                        $obj->food_items[] = [
                            'name' => $food['name'] ?? 'Unknown',
                            'quantity' => (int) ($food['quantity'] ?? 1),
                            'price' => (float) ($food['price'] ?? 0),
                        ];
                    }
                }

                if (!empty($bookingData['selected_activities'])) {
                    foreach ($bookingData['selected_activities'] as $activity) {
                        $obj->activity_items[] = [
                            'name' => $activity['name'] ?? 'Unknown',
                            'quantity' => (int) ($activity['guests'] ?? $activity['quantity'] ?? 1),
                            'price' => (float) ($activity['price'] ?? 0),
                        ];
                    }
                }

                if (!empty($bookingData['selected_activity_packages'])) {
                    foreach ($bookingData['selected_activity_packages'] as $package) {
                        $obj->activity_items[] = [
                            'name' => $package['name'] ?? 'Unknown Package',
                            'quantity' => (int) ($package['quantity'] ?? 1),
                            'price' => (float) ($package['price'] ?? 0),
                        ];
                    }
                }

                if (!empty($bookingData['num_pets'])) {
                    $obj->num_pets = (int) ($bookingData['num_pets'] ?? 0);
                }
                if (!empty($bookingData['has_pets'])) {
                    $obj->has_pets = (bool) ($bookingData['has_pets'] ?? false);
                }
                if (!empty($bookingData['pet_price'])) {
                    $obj->pet_fee = (float) ($bookingData['pet_price'] ?? 0);
                }

                if (!empty($bookingData['additional_adults'])) {
                    $obj->additional_adults = (int) ($bookingData['additional_adults'] ?? 0);
                }
                if (!empty($bookingData['additional_adult_price'])) {
                    $obj->additional_adult_fee = (float) ($bookingData['additional_adult_price'] ?? 0);
                }
                if (!empty($bookingData['mattress_price'])) {
                    $obj->additional_adult_fee = (float) ($bookingData['mattress_price'] ?? 0);
                }
            }

            return $this->applyDerivedPaymentLifecycleState($obj, $pendingCutoff);
        });

        return $bookingsCollection->concat($packageCollection)->sortByDesc(function ($item) {
            return $item->created_at;
        })->values();
    }

    private function renderExcelWorkbook(array $sections, string $rangeLabel, string $generatedAt): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;font-size:12px;color:#111;">';
        $html .= '<h1 style="font-size:20px;margin-bottom:6px;">CalaiaPH Admin Bookings Export</h1>';
        $html .= '<p style="margin:0 0 18px 0;">Range: ' . e($rangeLabel) . ' | Generated: ' . e($generatedAt) . '</p>';

        foreach ($sections as $section) {
            $title = e($section['title'] ?? 'Section');
            $headers = $section['headers'] ?? [];
            $rows = $section['rows'] ?? [];

            $html .= '<h2 style="font-size:16px;margin:20px 0 8px 0;">' . $title . '</h2>';
            $html .= '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:100%;margin-bottom:18px;">';
            $html .= '<thead><tr style="background:#f3f4f6;">';
            foreach ($headers as $header) {
                $html .= '<th style="text-align:left;">' . e($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            if (empty($rows)) {
                $html .= '<tr><td colspan="' . count($headers) . '">No rows found</td></tr>';
            } else {
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($headers as $header) {
                        $value = is_array($row) ? ($row[$header] ?? '') : '';
                        $html .= '<td>' . e($this->formatExcelValue($value)) . '</td>';
                    }
                    $html .= '</tr>';
                }
            }

            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    private function formatExcelValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('M d, Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_float($value) || is_int($value)) {
            return is_float($value)
                ? number_format($value, 2, '.', '')
                : (string) $value;
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function normalizePaymentScope(Request $request): string
    {
        $scope = (string) $request->query('payment_scope', 'operational');

        return in_array($scope, ['operational', 'all', 'attempts'], true) ? $scope : 'operational';
    }

    private function usesPaymentAttemptDate(Request $request, string $paymentScope): bool
    {
        if ($paymentScope === 'attempts') {
            return true;
        }

        return in_array((string) $request->query('payment_status'), ['pending', 'failed', 'attempts'], true);
    }

    private function applyDateRangeFilter(Builder $query, Request $request, string $dateColumn): void
    {
        // whereDate keeps the selected end date inclusive for timestamp columns too.
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->input('date_to'));
        }
    }

    private function applyPaymentLifecycleFilters(Builder $query, Request $request, string $paymentScope, Carbon $pendingCutoff): void
    {
        if ($request->filled('payment_status')) {
            $paymentStatus = (string) $request->string('payment_status');

            if ($paymentStatus === 'attempts') {
                $query->whereIn('payment_status', ['pending', 'failed']);
                return;
            }

            if ($paymentStatus === 'pending') {
                $query->where('payment_status', 'pending')
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->where('created_at', '>=', $pendingCutoff);
                return;
            }

            if ($paymentStatus === 'failed') {
                $query->where(function ($attempts) use ($pendingCutoff) {
                    $attempts->where('payment_status', 'failed')
                        ->orWhere(function ($stalePending) use ($pendingCutoff) {
                            $stalePending->where('payment_status', 'pending')
                                ->whereNotIn('status', ['cancelled', 'completed'])
                                ->where('created_at', '<', $pendingCutoff);
                        });
                });
                return;
            }

            $query->where('payment_status', $paymentStatus);
            return;
        }

        if ($paymentScope === 'all') {
            return;
        }

        if ($paymentScope === 'attempts') {
            $query->whereIn('payment_status', ['pending', 'failed']);
            return;
        }

        $query->whereNotIn('payment_status', ['pending', 'failed']);
    }

    private function pendingCutoff(): Carbon
    {
        return now()->subMinutes((int) config('booking.payment_attempt_expiry_minutes', 1440));
    }

    private function summarizeStaycationAccommodations(array $bookingData): array
    {
        $nameCounts = [];
        $unitCount = 0;
        $guestCount = 0;

        foreach ($bookingData['accommodations'] ?? [] as $accommodation) {
            if (!is_array($accommodation)) {
                continue;
            }

            $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
            $name = trim((string) (
                $accommodation['staycation']['name']
                ?? $accommodation['name']
                ?? $accommodation['accommodation_name']
                ?? ''
            ));

            if ($name === '' && $staycationId) {
                $name = (string) Staycation::query()->whereKey($staycationId)->value('name');
            }

            $normalizedName = strtolower($name);
            $usesUnitQuantity = str_contains($normalizedName, 'byot')
                || str_contains($normalizedName, 'bring your own')
                || str_contains($normalizedName, 'car camping');
            $isLegacyAggregatedRoom = ! $usesUnitQuantity
                && ! array_key_exists('adults', $accommodation)
                && ! array_key_exists('children', $accommodation)
                && array_key_exists('price', $accommodation)
                && array_key_exists('subtotal', $accommodation);
            $units = ($usesUnitQuantity || $isLegacyAggregatedRoom)
                ? max(1, (int) ($accommodation['quantity'] ?? 1))
                : 1;

            $name = $name !== '' ? $name : 'Staycation';
            $nameCounts[$name] = ($nameCounts[$name] ?? 0) + $units;
            $unitCount += $units;
            $guestCount += max(0, (int) ($accommodation['adults'] ?? 0))
                + max(0, (int) ($accommodation['children'] ?? $accommodation['children_total'] ?? 0));
        }

        $labels = [];
        foreach ($nameCounts as $name => $count) {
            $labels[] = $count > 1 ? "{$name} x{$count}" : $name;
        }

        if ($guestCount === 0) {
            $guestCount = max(0, (int) ($bookingData['total_guests'] ?? $bookingData['adults_total'] ?? $bookingData['guests'] ?? 0));
        }
        if ($guestCount === 0) {
            $guestCount = max(0, (int) ($bookingData['adults'] ?? 0))
                + max(0, (int) ($bookingData['children'] ?? 0));
        }

        return [
            'label' => $labels !== [] ? implode(', ', $labels) : null,
            'unit_count' => $unitCount,
            'guest_count' => $guestCount,
        ];
    }

    private function expireStalePendingBookings(Carbon $pendingCutoff, PaymentService $paymentService): void
    {
        Booking::query()
            ->where('payment_method', 'paymongo')
            ->where('payment_status', 'pending')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '<', $pendingCutoff)
            ->orderBy('id')
            ->get()
            ->each(function (Booking $booking) use ($paymentService) {
                $paymentStatus = $paymentService->getCheckoutPaymentStatus($booking->paymongo_checkout_id);

                if (($paymentStatus['status'] ?? null) === 'paid') {
                    $paymentService->confirmPendingBooking($booking, [
                        'checkout_id' => $booking->paymongo_checkout_id,
                        'payment_reference' => $paymentStatus['payment_reference'] ?? null,
                        'paid_amount_centavos' => $paymentStatus['paid_amount_centavos'] ?? null,
                    ]);
                    return;
                }

                if (($paymentStatus['status'] ?? null) === 'unknown') {
                    return;
                }

                $paymentService->failPendingBooking(
                    $booking,
                    $booking->paymongo_checkout_id,
                    'expired',
                    'stale_timeout'
                );
            });

        PackageBooking::query()
            ->where('status', 'pending_payment')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', 'pending');
            })
            ->where('created_at', '<', $pendingCutoff)
            ->orderBy('id')
            ->get()
            ->each(function (PackageBooking $booking) use ($paymentService) {
                $paymentStatus = $paymentService->getCheckoutPaymentStatus($booking->paymongo_reference);

                if (($paymentStatus['status'] ?? null) === 'paid') {
                    PaymongoWebhookService::markPackageBookingPaid(
                        $booking,
                        $paymentStatus['payment_reference'] ?? $booking->paymongo_reference,
                        $paymentStatus['paid_amount_centavos'] ?? null
                    );
                    return;
                }

                if (($paymentStatus['status'] ?? null) === 'unknown') {
                    return;
                }

                $booking->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            });
    }

    private function applyDerivedPaymentLifecycleState(object $booking, Carbon $pendingCutoff): object
    {
        $paymentStatus = strtolower((string) ($booking->payment_status ?? 'pending'));
        $status = strtolower((string) ($booking->status ?? ''));
        $attemptState = data_get($booking, 'booking_data.payment_attempt.state');
        $createdAt = $booking->created_at instanceof Carbon ? $booking->created_at : Carbon::parse($booking->created_at);
        $isStalePending = $paymentStatus === 'pending'
            && !in_array($status, ['cancelled', 'completed'], true)
            && $createdAt->lt($pendingCutoff);

        $booking->effective_payment_status = $isStalePending ? 'failed' : $paymentStatus;
        $booking->effective_attempt_state = in_array($paymentStatus, ['paid', 'refunded'], true)
            ? null
            : ($isStalePending
                ? 'expired'
                : ($attemptState ?: ($paymentStatus === 'pending' ? 'pending' : null)));
        $booking->is_stale_pending = $isStalePending;
        $booking->is_live_pending = $paymentStatus === 'pending' && !$isStalePending;

        return $booking;
    }

    private function extractSelectedPackages(array $bookingData): array
    {
        if (!empty($bookingData['packages']) && is_array($bookingData['packages'])) {
            return collect($bookingData['packages'])
                ->map(function ($package) {
                    $guests = (int) ($package['guests'] ?? 1);
                    $quantity = (int) ($package['quantity'] ?? 1);
                    $pricePerPerson = (float) ($package['price_per_person'] ?? 0);

                    return [
                        'id' => $package['id'] ?? null,
                        'package_id' => $package['package_id'] ?? null,
                        'name' => (string) ($package['name'] ?? 'Selected Package'),
                        'guests' => $guests,
                        'quantity' => $quantity,
                        'price_per_person' => $pricePerPerson,
                        'total_price' => (float) ($package['total_price'] ?? ($pricePerPerson * $guests * $quantity)),
                    ];
                })
                ->values()
                ->all();
        }

        $packageName = trim((string) ($bookingData['package'] ?? ''));
        $packageId = $bookingData['package_id'] ?? null;

        if ($packageName === '' && $packageId) {
            $packageName = optional(DayTour::find($packageId))->name ?? '';
        }

        if ($packageName === '') {
            return [];
        }

        $guests = (int) ($bookingData['guests'] ?? $bookingData['total_guests'] ?? 1);
        $totalPrice = (float) ($bookingData['price'] ?? $bookingData['total_price'] ?? 0);
        $pricePerPerson = $guests > 0 ? round($totalPrice / $guests, 2) : $totalPrice;

        return [[
            'id' => $packageId,
            'package_id' => is_numeric($packageId) ? (int) $packageId : null,
            'name' => $packageName,
            'guests' => $guests,
            'quantity' => 1,
            'price_per_person' => $pricePerPerson,
            'total_price' => $totalPrice,
        ]];
    }

    private function extractFoodItems(array $bookingData): array
    {
        return collect($bookingData['selected_foods'] ?? [])
            ->filter(fn ($food) => is_array($food) && !empty($food['name']))
            ->map(function ($food) {
                $quantity = (int) ($food['quantity'] ?? 1);
                $unitPrice = (float) ($food['price'] ?? 0);

                return [
                    'id' => $food['id'] ?? null,
                    'name' => (string) $food['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ];
            })
            ->values()
            ->all();
    }

    private function extractSelectedActivities(array $bookingData): array
    {
        return collect($bookingData['selected_activities'] ?? [])
            ->filter(fn ($activity) => is_array($activity) && !empty($activity['name']))
            ->map(function ($activity) {
                $quantity = (int) ($activity['guests'] ?? $activity['quantity'] ?? 1);
                $unitPrice = (float) ($activity['price'] ?? 0);

                return [
                    'id' => $activity['id'] ?? null,
                    'name' => (string) $activity['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ];
            })
            ->values()
            ->all();
    }

    private function extractSelectedActivityPackages(array $bookingData): array
    {
        return collect($bookingData['selected_activity_packages'] ?? [])
            ->filter(fn ($package) => is_array($package) && !empty($package['name']))
            ->map(function ($package) {
                $quantity = (int) ($package['quantity'] ?? 1);
                $unitPrice = (float) ($package['price'] ?? 0);

                return [
                    'id' => $package['id'] ?? null,
                    'package_id' => $package['package_id'] ?? null,
                    'name' => (string) $package['name'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ];
            })
            ->values()
            ->all();
    }
}
