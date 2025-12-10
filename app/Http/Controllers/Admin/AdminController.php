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
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function staycation()
    {
        try {
            $staycations = Staycation::with(['swiperImages', 'texts'])->get();
            $images = StaycationSwiperImage::all();
            $texts = StaycationText::all();
            return view('admin.whereToStay.staycations', compact('staycations', 'images', 'texts'));
        } catch (\Exception $e) {
            Log::error('Error fetching staycations: ' . $e->getMessage());
            return back()->with('error', 'Failed to load staycations. Please try again.');
        }
    }

    public function activity()
    {
        try {
            $activities = Activity::with(['swiperImages', 'texts'])->get();
            $images = ActivitySwiperImage::all();
            $texts = ActivityText::all();
            return view('admin.whatToDo.activities', compact('activities', 'images', 'texts'));
        } catch (\Exception $e) {
            Log::error('Error fetching activities: ' . $e->getMessage());
            return back()->with('error', 'Failed to load activities. Please try again.');
        }
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
        try {
            $tours = DayTour::with('activities')->get();
            $activities = Activity::all();
            $farmInfos = FarmInfo::with(['swiperImages', 'texts'])->get();
            $images = FarmInfoSwiperImage::all();
            $texts = DayPackageText::all();
            return view('admin.dayTour.days', compact('tours', 'texts', 'activities', 'farmInfos', 'images'));
        } catch (\Exception $e) {
            Log::error('Error fetching day tours: ' . $e->getMessage());
            return back()->with('error', 'Failed to load day tours. Please try again.');
        }
    }

    public function staycationAccommodation()
    {
        try {
            $accommodations = StaycationAccommodation::with('accommodationVariants')->get();
            $staycations = Staycation::all();
            $texts = AccommodationText::all();
            return view('admin.whereToStay.accommodations', compact('accommodations', 'texts', 'staycations'));
        } catch (\Exception $e) {
            Log::error('Error fetching staycation accommodations: ' . $e->getMessage());
            return back()->with('error', 'Failed to load staycation accommodations. Please try again.');
        }
    }
}
