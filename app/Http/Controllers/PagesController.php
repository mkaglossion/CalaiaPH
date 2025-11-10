<?php

namespace App\Http\Controllers;

use App\Models\SwiperImage;
use App\Models\DayTourSwiperImage;
use App\Models\DoSwiperImage;
use App\Models\FarmSwiperImage;
use App\Models\FoodSwiperImage;
use App\Models\SpecialSwiperImage;
use App\Models\StaySwiperImage;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function home()
    {
        $swiperImages  = SwiperImage::all();
        $active = 'home';
        return view('home', compact('swiperImages', 'active'));
    }

    public function daytour()
    {
        $swiperImages = DayTourSwiperImage::all();
        $active = 'daytour';
        return view('daytour', compact('swiperImages', 'active'));
    }

    public function staycation()
    {
        $swiperImages = StaySwiperImage::all();
        $active = 'staycation';
        return view('staycation', compact('swiperImages', 'active'));
    }

    public function activities()
    {
        $swiperImages = DoSwiperImage::all();
        $active = 'activities';
        return view('activities', compact('swiperImages', 'active'));
    }

    public function dining()
    {
        $swiperImages = FoodSwiperImage::all();
        $active = 'dining';
        return view('dining', compact('swiperImages', 'active'));
    }

    public function groups()
    {
        $swiperImages = SpecialSwiperImage::all();
        $active = 'groups';
        return view('groups', compact('swiperImages', 'active'));
    }

    public function farm()
    {
        $swiperImages = FarmSwiperImage::all();
        $active = 'farm';
        return view('farm', compact('swiperImages', 'active'));
    }
}