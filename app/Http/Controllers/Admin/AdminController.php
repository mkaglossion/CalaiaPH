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


class AdminController extends Controller
{
    public function staycation()
    {
        $staycations = Staycation::all();
        $images = StaycationSwiperImage::all();
        $texts = StaycationText::all();
        return view('admin.staycations', compact('staycations', 'images', 'texts'));
    }

    public function activity()
    {
        $activities = Activity::all();
        $images = ActivitySwiperImage::all();
        $texts = ActivityText::all();
        return view('admin.activities', compact('activities', 'images', 'texts'));
    }

    public function menu()
    {
        $menus = Menu::all();
        $images = MenuSwiperImage::all();
        $texts = MenuText::all();
        return view('admin.menus', compact('menus', 'images', 'texts'));
    }

    public function event()
    {
        $events = Event::all();
        $images = EventSwiperImage::all();
        $texts = EventText::all();
        return view('admin.events', compact('events', 'images', 'texts'));
    }

    public function farmInfo()
    {
        $farmInfos = FarmInfo::all();
        $images = FarmInfoSwiperImage::all();
        $texts = FarmInfoText::all();
        return view('admin.farmInfos', compact('farmInfos', 'images', 'texts'));
    }
}
