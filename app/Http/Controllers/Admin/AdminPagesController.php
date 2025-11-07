<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\StaySwiperImage;
use App\Models\StayText;
use App\Models\DoSwiperImage;
use App\Models\DoText;
use App\Models\FoodSwiperImage;
use App\Models\FoodText;
use App\Models\SpecialSwiperImage;
use App\Models\SpecialText;
use App\Models\FarmSwiperImage;
use App\Models\FarmText;
use App\Models\SwiperImage;
use App\Models\HomeText;

class AdminPagesController extends Controller
{
    public function home()
    {
        $images = SwiperImage::all();
        $texts = HomeText::all();
        return view('admin.pages.home', compact('images', 'texts'));
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
        $texts = StayText::all();
        return view('admin.pages.wheretostay', compact('images', 'texts'));
    }
    public function whatToDo()
    {
        $images = DoSwiperImage::all();
        $texts = DoText::all();
        return view('admin.pages.whattodo', compact('images', 'texts'));
    }
    public function foodAndBeverage()
    {
        $images = FoodSwiperImage::all();
        $texts = FoodText::all();
        return view('admin.pages.foodandbeverage', compact('images', 'texts'));
    }
    public function specialEvents()
    {
        $images = SpecialSwiperImage::all();
        $texts = SpecialText::all();
        return view('admin.pages.specialevents', compact('images', 'texts'));
    }
    public function ourFarm()
    {
        $images = FarmSwiperImage::all();
        $texts = FarmText::all();
        return view('admin.pages.ourfarm', compact('images', 'texts'));
    }
}
