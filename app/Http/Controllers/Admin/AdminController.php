<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
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
            $staycation->today_available = $inventory ? $inventory->available_quantity : ($staycation->quantity ?? 0);
        }
        
        $images = StaycationSwiperImage::all();
        $texts = StaycationText::all();
        return view('admin.whereToStay.staycations', compact('staycations', 'images', 'texts'));
    }

    public function activity()
    {
        $activities = Activity::orderBy('sort_order')->paginate(10);
        $images = ActivitySwiperImage::all();
        $texts = ActivityText::all();
        return view('admin.whatToDo.activities', compact('activities', 'images', 'texts'));
    }

    public function menu()
    {
        $menus = Menu::all();
        $images = MenuSwiperImage::all();
        $texts = MenuText::all();
        return view('admin.foodBeverages.menus', compact('menus', 'images', 'texts'));
    }

    public function event()
    {
        $events = Event::all();
        $images = EventSwiperImage::all();
        $texts = EventText::all();
        return view('admin.specialEvents.events', compact('events', 'images', 'texts'));
    }

    public function farmInfo()
    {
        $farmInfos = FarmInfo::all();
        $images = FarmInfoSwiperImage::all();
        $texts = FarmInfoText::all();
        return view('admin.ourFarm.farmInfos', compact('farmInfos', 'images', 'texts'));
    }

    public function dayTour()
    {
        $tours  = DayTour::with('activities')->get(); // get all packages with activities
        $texts = DayPackageText::all();
        $activities = Activity::all(); // list of all activities for selection
        return view('admin.dayTour.days', compact('tours', 'texts', 'activities'));
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
        $images = FarmSwiperImage::all();
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
        $query->ordered();

        $testimonials = $query->ordered()->paginate(10);

        return view('admin.testimonials.index', compact('testimonials'));
    }


}
