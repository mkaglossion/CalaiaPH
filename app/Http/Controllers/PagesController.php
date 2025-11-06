<?php

namespace App\Http\Controllers;

use App\Models\SwiperImage;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function daytour()
    {
        $images = SwiperImage::where('page', 'daytour')->get();
        return view('daytour', compact('images'));
    }

    public function staycation()
    {
        $images = SwiperImage::where('page', 'staycation')->get();
        return view('staycation', compact('images'));
    }

    public function activities()
    {
        $images = SwiperImage::where('page', 'activities')->get();
        return view('activities', compact('images'));
    }

    public function dining()
    {
        $images = SwiperImage::where('page', 'dining')->get();
        return view('dining', compact('images'));
    }

    public function groups()
    {
        $images = SwiperImage::where('page', 'groups')->get();
        return view('groups', compact('images'));
    }

    public function farm()
    {
        $images = SwiperImage::where('page', 'farm')->get();
        return view('farm', compact('images'));
    }
}