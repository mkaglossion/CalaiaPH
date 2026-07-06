<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\ActivitySwiperImage;
use App\Models\ActivityText;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use App\Models\StaycationText;
use App\Models\StaycationInventory;
use App\Models\Menu;
use App\Models\MenuSwiperImage;
use App\Models\MenuText;
use App\Models\Event;
use App\Models\EventSwiperImage;
use App\Models\EventText;
use App\Models\FarmInfo;
use App\Models\FarmInfoSwiperImage;
use App\Models\FarmInfoText;
use App\Models\DayTour;
use App\Models\DayPackageText;
use App\Models\StaycationAccommodation;
use App\Models\AccommodationText;
use App\Models\PeakPeriod;
use App\Models\PeakType;
use App\Models\FarmSwiperImage;
use App\Models\FarmText;
use App\Models\Product;
use App\Models\ShopOrder;
use App\Models\ShopText;
use App\Models\ShopSwiperImage;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourInventory;
use App\Services\PricingService;

use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function staycation(Request $request)
    {
        $sortBy = $request->string('sort_by', 'featured')->toString();
        $sortOptions = [
            'featured' => 'Featured (Saved Order)',
            'name_asc' => 'Alphabetically, A-Z',
            'name_desc' => 'Alphabetically, Z-A',
            'price_asc' => 'Price, low to high',
            'price_desc' => 'Price, high to low',
            'oldest' => 'Date, old to new',
            'newest' => 'Date, new to old',
        ];
        if (!array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'featured';
        }

        $staycationsQuery = Staycation::query();
        switch ($sortBy) {
            case 'name_asc':
                $staycationsQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $staycationsQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $staycationsQuery->orderByRaw('COALESCE(price, 0) asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $staycationsQuery->orderByRaw('COALESCE(price, 0) desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $staycationsQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $staycationsQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $staycationsQuery->ordered();
                break;
        }

        $staycations = $staycationsQuery->get();

        // Add today's available inventory to each staycation
        $today = now()->format('Y-m-d');
        foreach ($staycations as $staycation) {
            // Get actual inventory from database, not fallback
            $inventory = StaycationInventory::where('staycation_id', $staycation->id)
                ->where('date', $today)
                ->first();

            // Use inventory available_quantity if exists, otherwise use base quantity
            $staycation->today_available = $inventory ? $inventory->available_quantity : ($staycation->base_quantity ?? 0);
        }

        // Get current season
        $currentSeasonPeriod = \App\Models\PeakPeriod::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with('peakType')
            ->orderBy(\App\Models\PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'), 'desc')
            ->first();

        $currentSeason = $currentSeasonPeriod ? $currentSeasonPeriod->peakType->name : 'Non-Peak';
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak',
        ];
        $currentSeasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';

        $images = StaycationSwiperImage::with('staycation')->latest()->get();
        $texts = $this->normalizeStaycationTexts(StaycationText::with('staycation')->orderBy('id')->get());
        $pricingService = app(PricingService::class);
        $todayCarbon = Carbon::today();

        $staycations->transform(function ($staycation) use ($pricingService, $todayCarbon, $currentSeasonKey, $currentSeason) {
            $staycation->setAttribute(
                'current_rate_preview',
                $pricingService->getStaycationRate($staycation, $todayCarbon, $currentSeasonKey)
            );
            $staycation->setAttribute('current_season_preview', $currentSeason);

            return $staycation;
        });

        return view('admin.whereToStay.staycations', compact('staycations', 'images', 'texts', 'currentSeason', 'currentSeasonKey', 'sortBy', 'sortOptions'));
    }

    private function normalizeStaycationTexts($texts)
    {
        return $texts
            ->groupBy(function ($text) {
                $section = strtolower(trim((string) ($text->section ?? '')));
                return $section . '|' . (string) ($text->staycation_id ?? '');
            })
            ->map(function ($group) {
                $latest = $group->sortByDesc('id')->first();
                if (!$latest) {
                    return null;
                }

                if (strtolower(trim((string) ($latest->section ?? ''))) === 'vr') {
                    $merged = $this->mergeVrTextGroup($group, $latest);
                    $latest = clone $latest;
                    $latest->content = json_encode($merged, JSON_UNESCAPED_SLASHES);
                }

                return $latest;
            })
            ->filter()
            ->values();
    }

    private function mergeVrTextGroup($group, $latest)
    {
        $merged = $this->decodeJsonTextContent($latest->content);
        $merged['images'] = [];
        $seen = [];

        foreach ($group->sortBy('id') as $text) {
            $content = $this->decodeJsonTextContent($text->content);
            $images = is_array($content['images'] ?? null) ? $content['images'] : [];

            foreach ($images as $image) {
                if (!is_array($image)) {
                    continue;
                }

                $key = trim((string) ($image['public_id'] ?? $image['src'] ?? ''));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $merged['images'][] = $image;
            }
        }

        return $merged;
    }

    private function decodeJsonTextContent(?string $content): array
    {
        if (!is_string($content) || trim($content) === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function staycationInventory()
    {
        $staycations = Staycation::ordered()->with('inventories')->get();
        return view('admin.whereToStay.inventory', compact('staycations'));
    }

    public function activity(Request $request)
    {
        $sortBy = $request->string('sort_by', 'featured')->toString();
        $sortOptions = [
            'featured' => 'Featured',
            'name_asc' => 'Alphabetically, A-Z',
            'name_desc' => 'Alphabetically, Z-A',
            'price_asc' => 'Price, low to high',
            'price_desc' => 'Price, high to low',
            'oldest' => 'Date, old to new',
            'newest' => 'Date, new to old',
        ];
        if (!array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'featured';
        }

        // Paginate activities first
        $activitiesQuery = Activity::query();
        switch ($sortBy) {
            case 'name_asc':
                $activitiesQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $activitiesQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $activitiesQuery->orderBy('price', 'asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $activitiesQuery->orderBy('price', 'desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $activitiesQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $activitiesQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $activitiesQuery->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
                break;
        }
        $activities = $activitiesQuery->paginate(10)->appends(['sort_by' => $sortBy]);
        
        // Load related data only for current page
        $activityIds = $activities->pluck('id')->toArray();
        
        // Limit images to 50 max and only for current page activities
        $images = ActivitySwiperImage::with('activity')
            ->whereIn('activity_id', $activityIds)
            ->limit(50)
            ->get();
            
        // Limit texts as well
        $texts = ActivityText::whereIn('activity_id', $activityIds)
            ->limit(50)
            ->get();
        
        // Load activity packages
        $activityPackages = ActivityPackage::with('activities')->latest()->get();
        
        // Load all activities for the activity selector in the modal
        $allActivities = Activity::all(['id', 'name', 'description', 'price']);
        
        return view('admin.whatToDo.activities', compact('activities', 'images', 'texts', 'activityPackages', 'allActivities', 'sortBy', 'sortOptions'));
    }

    public function menu()
    {
        try {
            $menus = Menu::with(['swiperImages', 'texts'])->get();
            $images = MenuSwiperImage::all();
            $texts = MenuText::all();
            return view('admin.foodBeverages.menus', compact('menus', 'images', 'texts'));
        } catch (\Exception $e) {
            Log::error('Error fetching menus: ' . $e->getMessage());
            return back()->with('error', 'Failed to load menus. Please try again.');
        }
    }

    public function event()
    {
        try {
            $events = Event::with(['swiperImages', 'texts'])->get();
            $images = EventSwiperImage::all();
            $texts = EventText::all();
            return view('admin.specialEvents.events', compact('events', 'images', 'texts'));
        } catch (\Exception $e) {
            Log::error('Error fetching events: ' . $e->getMessage());
            return back()->with('error', 'Failed to load events. Please try again.');
        }
    }

    public function farmInfo()
    {
        try {
            $farmInfos = FarmInfo::with(['swiperImages', 'texts'])->get();
            $images = FarmInfoSwiperImage::all();
            $texts = FarmInfoText::all();
            return view('admin.ourFarm.farmInfos', compact('farmInfos', 'images', 'texts'));
        } catch (\Exception $e) {
            Log::error('Error fetching farm infos: ' . $e->getMessage());
            return back()->with('error', 'Failed to load farm infos. Please try again.');
        }
    }

    public function dayTour()
    {
        $tours  = DayTour::with(['activities', 'swiperImages'])->get(); // get all packages with activities
        $texts = DayPackageText::all();
        $activities = Activity::all(); // list of all activities for selection
        $images = DayTourSwiperImage::latest()->get();

        // Fetch info section data
        $infoTitle = \App\Models\DayTourText::where('title', 'info_title')->first();
        $infoSubtitle = \App\Models\DayTourText::where('title', 'info_subtitle')->first();
        $infoItemsJson = \App\Models\DayTourText::where('title', 'info_items')->first();
        $infoItems = $infoItemsJson ? json_decode($infoItemsJson->content, true) : [];

        return view('admin.dayTour.days', compact('tours', 'texts', 'activities', 'images', 'infoTitle', 'infoSubtitle', 'infoItems', 'infoItemsJson'));
    }

    public function staycationAccommodation()
    {
        $accommodations  = StaycationAccommodation::with('variants')->get(); // get all packages with activities
        $texts = AccommodationText::all();
        $staycations = Staycation::ordered()->get(); // list of all activities for selection
        return view('admin.whereToStay.accommodations', compact('accommodations', 'texts', 'staycations'));
    }

    public function shopstore()
    {
        $images = ShopSwiperImage::all();
        $texts = ShopText::latest()->get();
        return view('admin.ShopStore.shopstore', compact('images', 'texts'));
    }

    public function shopitems()
    {
        $products = Product::paginate(10);
        return view('admin.ShopStore.shopitems', compact('products'));
    }

    public function shopOrders(Request $request)
    {
        $query = ShopOrder::query()->latest();

        if ($request->filled('q')) {
            $term = trim((string) $request->string('q'));
            $query->where(function ($q) use ($term) {
                $q->where('order_reference', 'like', '%' . $term . '%')
                    ->orWhere('transaction_id', 'like', '%' . $term . '%')
                    ->orWhere('paymongo_checkout_id', 'like', '%' . $term . '%')
                    ->orWhere('customer_name', 'like', '%' . $term . '%')
                    ->orWhere('customer_email', 'like', '%' . $term . '%')
                    ->orWhere('customer_phone', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', (string) $request->string('payment_status'));
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', (string) $request->string('order_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => ShopOrder::count(),
            'pending' => ShopOrder::where('payment_status', ShopOrder::PAYMENT_STATUS_PENDING)->count(),
            'paid' => ShopOrder::where('payment_status', ShopOrder::PAYMENT_STATUS_PAID)->count(),
            'failed' => ShopOrder::where('payment_status', ShopOrder::PAYMENT_STATUS_FAILED)->count(),
            'paid_revenue' => (float) ShopOrder::where('payment_status', ShopOrder::PAYMENT_STATUS_PAID)->sum('final_total'),
        ];

        return view('admin.ShopStore.orders', compact('orders', 'stats'));
    }

    public function eventExperience()
    {
        $sections = \App\Models\EventExperienceSection::ordered()->get();
        return view('admin.eventExperience.sections', compact('sections'));
    }

    public function testimonials(Request $request)
    {
        $query = \App\Models\Testimonial::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Default ordering
        $testimonials = $query->ordered()->paginate(10);


        // Get the next available order number (highest order + 1)
        $maxOrder = \App\Models\Testimonial::max('order') ?? 0;
        $nextOrder = $maxOrder + 1;
        $sectionBackground = \App\Models\TestimonialSectionSetting::current();

        return view('admin.testimonials.index', compact('testimonials', 'nextOrder', 'sectionBackground'));
    }

    public function staySettings()
    {
        // Get all staycations with their pricing configurations
        $staycations = Staycation::ordered()->get();
        $pricingService = app(PricingService::class);
        $today = Carbon::today();
        $currentSeasonPeriod = PeakPeriod::where('start_date', '<=', $today->format('Y-m-d'))
            ->where('end_date', '>=', $today->format('Y-m-d'))
            ->with('peakType')
            ->orderBy(
                PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'),
                'desc'
            )
            ->first();

        $currentSeason = $pricingService->getSeasonType($today) ?? 'Non-Peak';
        $currentDayType = $pricingService->getDayType($today);
        $currentSeasonRange = $currentSeasonPeriod
            ? [
                'name' => $currentSeasonPeriod->name,
                'start_date' => Carbon::parse($currentSeasonPeriod->start_date)->format('M j, Y'),
                'end_date' => Carbon::parse($currentSeasonPeriod->end_date)->format('M j, Y'),
            ]
            : null;
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak',
        ];
        $todaySeasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';

        // Group staycations by their pricing configurations
        $pricingByStaycation = [];
        foreach ($staycations as $staycation) {
            $name = $staycation->name;
            $rates = [
                'nonPeak' => 0,
                'peak' => 0,
                'superPeak' => 0,
            ];

            foreach (($staycation->price_configs ?? []) as $config) {
                if (!is_array($config) || !isset($config['rates']) || !is_array($config['rates'])) {
                    continue;
                }

                foreach (array_keys($rates) as $rateKey) {
                    $rateValue = (float) ($config['rates'][$rateKey] ?? 0);
                    if ($rates[$rateKey] <= 0 && $rateValue > 0) {
                        $rates[$rateKey] = $rateValue;
                    }
                }
            }

            $currentPostedPrice = $pricingService->getStaycationRate($staycation, $today->copy(), $todaySeasonKey);
            if ($currentPostedPrice <= 0) {
                $currentPostedPrice = (float) ($staycation->price ?? 0);
            }

            $pricingByStaycation[$name] = $rates + [
                'currentPostedPrice' => $currentPostedPrice,
            ];
        }

        return view('admin.stay.settings', compact(
            'currentSeason',
            'currentDayType',
            'currentSeasonRange',
            'pricingByStaycation',
            'staycations'
        ));
    }

    public function dayTourDays()
    {
        $packages = DayTour::all();
        $tours = $packages; // for backward compatibility
        $texts = DayPackageText::all();
        $activities = Activity::all();
        $images = DayTourSwiperImage::all();

        // Get Important Information data
        $infoTitle = \App\Models\DayTourText::where('title', 'info_title')->first();
        $infoSubtitle = \App\Models\DayTourText::where('title', 'info_subtitle')->first();
        $infoItemsJson = \App\Models\DayTourText::where('title', 'info_items')->first();
        $infoItems = $infoItemsJson ? json_decode($infoItemsJson->content, true) : [];

        return view('admin.dayTour.days', compact('packages', 'tours', 'texts', 'activities', 'images', 'infoTitle', 'infoSubtitle', 'infoItems', 'infoItemsJson'));
    }

    public function dayTourInventory(Request $request)
    {
        // Date range filter
        $dateFrom = $request->input('date_from') 
            ? Carbon::parse($request->input('date_from')) 
            : Carbon::today();
        $dateTo = $request->input('date_to') 
            ? Carbon::parse($request->input('date_to')) 
            : Carbon::today()->addDays(30);

        // Ensure date range is valid
        if ($dateFrom->gt($dateTo)) {
            $temp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $temp;
        }

        // Get all active day tour packages
        $packages = DayTour::where('is_active', true)->get();

        // Build inventory data for each package
        $inventoryData = [];
        foreach ($packages as $package) {
            $inventories = DayTourInventory::where('day_tour_id', $package->id)
                ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                ->orderBy('date')
                ->get();

            // If no inventory records exist, create default entries as stdClass objects
            if ($inventories->isEmpty()) {
                $defaultMaxGroups = $package->max_groups_per_day ?? 3;
                $dates = [];
                $current = $dateFrom->copy();
                while ($current->lte($dateTo)) {
                    $item = new \stdClass();
                    $item->date = $current->format('Y-m-d');
                    $item->max_groups = $defaultMaxGroups;
                    $item->booked_groups = 0;
                    $item->available_groups = $defaultMaxGroups;
                    $item->is_blocked = false;
                    $item->block_reason = null;
                    $dates[] = $item;
                    $current->addDay();
                }
                $inventories = collect($dates);
            }

            $inventoryData[] = [
                'package' => $package,
                'inventories' => $inventories,
            ];
        }

        // Calculate daily totals across all packages
        $dailyTotals = [];
        $current = $dateFrom->copy();
        while ($current->lte($dateTo)) {
            $dateStr = $current->format('Y-m-d');
            $totalAvailable = 0;
            $totalMaxGroups = 0;

            foreach ($inventoryData as $data) {
                $inventory = $data['inventories']->firstWhere('date', $dateStr);
                if ($inventory) {
                    $totalAvailable += $inventory->available_groups ?? 0;
                    $totalMaxGroups += $inventory->max_groups ?? ($data['package']->max_groups_per_day ?? 3);
                } else {
                    // Use default if no record
                    $defaultGroups = $data['package']->max_groups_per_day ?? 3;
                    $totalAvailable += $defaultGroups;
                    $totalMaxGroups += $defaultGroups;
                }
            }


            $dailyTotals[] = [
                'date' => $dateStr,
                'total_available' => $totalAvailable,
                'total_max_groups' => $totalMaxGroups,
            ];

            $current->addDay();
        }

        // Paginate the daily totals
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $total = count($dailyTotals);
        
        // Slice the data for the current page
        $offset = ($currentPage - 1) * $perPage;
        $paginatedTotals = array_slice($dailyTotals, $offset, $perPage);

        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedTotals,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Also need to paginate the inventory data for each package
        $paginatedInventoryData = [];
        foreach ($inventoryData as $data) {
            $paginatedInventories = $data['inventories']->slice($offset, $perPage)->values();
            $paginatedInventoryData[] = [
                'package' => $data['package'],
                'inventories' => $paginatedInventories,
            ];
        }

        return view('admin.dayTour.inventory', [
            'packages' => $packages,
            'inventoryData' => $paginatedInventoryData,
            'dailyTotals' => $paginatedTotals,
            'pagination' => $pagination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function dayTourInventoryManagement()
    {
        $dayTours = DayTour::with(['inventory' => function ($query) {
            $query->where('date', '>=', Carbon::today())->orderBy('date');
        }])->where('is_active', true)->get();
        return view('admin.dayTour.inventory', compact('dayTours'));
    }

    public function specialEventsManager()
    {
        $events = Event::all();
        return view('admin.specialEvents.manager', compact('events'));
    }

    public function packages()
    {
        $packages = DayTour::with(['texts', 'swiperImages', 'activities'])->get();
        return view('admin.packages.index', compact('packages'));
    }
}
