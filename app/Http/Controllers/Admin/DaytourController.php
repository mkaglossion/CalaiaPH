<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;


class DaytourController extends Controller
{
    public function index()
    {
        $images = DayTourSwiperImage::all();
        $texts = DayTourText::all();
        return view('admin.pages.daytour', compact('images', 'texts'));
    }
}
