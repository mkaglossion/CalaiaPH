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
use App\Models\DayType;
use App\Models\PeakType;
use App\Models\PeakPeriod;
use App\Models\Holiday;
use App\Models\DurationSet;
use App\Models\PricingConfig;
use App\Models\FarmSwiperImage;
use App\Models\FarmText;
use App\Models\Product;
use App\Models\ShopSwiperImage;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourInventory;

use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function staycation()
    {
        $staycations = Staycation::all();

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

        $images = StaycationSwiperImage::with('staycation')->latest()->get();
        $texts = StaycationText::all();
        return view('admin.whereToStay.staycations', compact('staycations', 'images', 'texts', 'currentSeason'));
    }

    public function staycationInventory()
    {
        $staycations = Staycation::with('inventories')->get();
        return view('admin.whereToStay.inventory', compact('staycations'));
    }

    public function activity()
    {
        // Paginate activities first
        $activities = Activity::orderBy('sort_order')->paginate(10);
        
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
        
        return view('admin.whatToDo.activities', compact('activities', 'images', 'texts', 'activityPackages', 'allActivities'));
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
        $staycations = Staycation::all(); // list of all activities for selection
        return view('admin.whereToStay.accommodations', compact('accommodations', 'texts', 'staycations'));
    }

    public function config()
    {
        $dayTypes = DayType::with('days')->get();
        $peakTypes = PeakType::with('periods')->get();
        $holidays = Holiday::all();
        $durationSets = DurationSet::all();
        $pricingConfigs = PricingConfig::with(['peakPeriod', 'holiday', 'durationSet', 'dayType'])->get();

        return view('admin.pricingConfig.index', compact(
            'dayTypes', 'peakTypes', 'holidays', 'durationSets', 'pricingConfigs'
        ));
    }

    public function shopstore()
    {
        $images = ShopSwiperImage::all();
        $texts = FarmText::all();
        return view('admin.ShopStore.shopstore', compact('images', 'texts'));
    }

    public function shopitems()
    {
        $products = Product::paginate(10);
        return view('admin.ShopStore.shopitems', compact('products'));
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

        return view('admin.testimonials.index', compact('testimonials', 'nextOrder'));
    }

    public function staySettings()
    {
        // Get all staycations with their pricing configurations
        $staycations = Staycation::all();

        // Get current season type by checking today's date
        $today = now()->format('Y-m-d');
        $currentSeasonPeriod = \App\Models\PeakPeriod::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with('peakType')
            ->orderBy(\App\Models\PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'), 'desc')
            ->first();

        $currentSeason = $currentSeasonPeriod ? $currentSeasonPeriod->peakType->name : 'Non-Peak';

        // Group staycations by their pricing configurations
        $pricingByStaycation = [];
        foreach ($staycations as $staycation) {
            $name = $staycation->name;
            $priceConfigs = $staycation->price_configs ?? [];

            if (!empty($priceConfigs) && isset($priceConfigs[0]['rates'])) {
                $rates = $priceConfigs[0]['rates'];
                $pricingByStaycation[$name] = [
                    'nonPeak' => $rates['nonPeak'] ?? 0,
                    'peak' => $rates['peak'] ?? 0,
                    'superPeak' => $rates['superPeak'] ?? 0,
                ];
            } else {
                $pricingByStaycation[$name] = [
                    'nonPeak' => 0,
                    'peak' => 0,
                    'superPeak' => 0,
                ];
            }
        }

        return view('admin.stay.settings', compact('currentSeason', 'pricingByStaycation', 'staycations'));
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
