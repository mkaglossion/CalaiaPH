<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\StaySwiperImage;
use App\Models\DoSwiperImage;
use App\Models\FoodSwiperImage;
use App\Models\SpecialSwiperImage;
use App\Models\FarmSwiperImage;
use App\Models\SwiperImage;

class AdminPagesController extends Controller
{
    public function home()
    {
        $images = SwiperImage::all();
        return view('admin.pages.home', compact('images'));
    }
    public function dayTour()
    {
        $images = DayTourSwiperImage::all();
        $texts = DayTourText::all();
        return view('admin.pages.daytour', compact('images', 'texts'));
    }
    public function whereToStay()
    {
        $images = StaySwiperImage::all();
        return view('admin.pages.wheretostay', compact('images'));
    }
    public function whatToDo()
    {
        $images = DoSwiperImage::all();
        return view('admin.pages.whattodo', compact('images'));
    }
    public function foodAndBeverage()
    {
        $images = FoodSwiperImage::all();
        return view('admin.pages.foodandbeverage', compact('images'));
    }
    public function specialEvents()
    {
        $images = SpecialSwiperImage::all();
        return view('admin.pages.specialevents', compact('images'));
    }
    public function ourFarm()
    {
        $images = FarmSwiperImage::all();
        return view('admin.pages.ourfarm', compact('images'));
    }
}
