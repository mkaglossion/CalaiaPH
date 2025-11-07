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
        return view('home', compact('swiperImages'));
    }

    public function daytour()
    {
        $swiperImages = DayTourSwiperImage::all();
        return view('daytour', compact('swiperImages'));
    }

    public function staycation()
    {
        $swiperImages = StaySwiperImage::all();
        return view('staycation', compact('swiperImages'));
    }

    public function activities()
    {
        $swiperImages = DoSwiperImage::all();
        return view('activities', compact('swiperImages'));
    }

    public function dining()
    {
        $swiperImages = FoodSwiperImage::all();
        return view('dining', compact('swiperImages'));
    }

    public function groups()
    {
        $swiperImages = SpecialSwiperImage::all();
        return view('groups', compact('swiperImages'));
    }

    public function farm()
    {
        $swiperImages = FarmSwiperImage::all();
        return view('farm', compact('swiperImages'));
    }
}