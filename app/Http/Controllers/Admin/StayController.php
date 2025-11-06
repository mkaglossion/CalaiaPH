<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaySwiperImage;

class StayController extends Controller
{
    public function index()
    {
        $images = StaySwiperImage::all();
        return view('admin.pages.wheretostay', compact('images'));
    }
}
