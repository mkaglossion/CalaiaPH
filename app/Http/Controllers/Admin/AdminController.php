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
}
