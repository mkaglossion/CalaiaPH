<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivitySwiperImage;
use App\Models\ActivityText;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use App\Models\StaycationText;
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



class AdminController extends Controller
{
    public function staycation()
    {
        $staycations = Staycation::all();
        $images = StaycationSwiperImage::all();
        $texts = StaycationText::all();
        return view('admin.whereToStay.staycations', compact('staycations', 'images', 'texts'));
    }

    public function activity()
    {
        $activities = Activity::all();
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

}
