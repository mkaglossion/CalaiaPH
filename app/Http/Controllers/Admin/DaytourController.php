<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;

class DaytourController extends Controller
{
    public function index()
    {
        $images = DayTourSwiperImage::all();
        return view('admin.pages.daytour', compact('images'));
    }
}
