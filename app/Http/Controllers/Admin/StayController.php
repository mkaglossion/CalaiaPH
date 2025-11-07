<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaySwiperImage;
use App\Models\StayText;

class StayController extends Controller
{
    public function index()
    {
        $images = StaySwiperImage::all();
        $texts = StayText::all();
        return view('admin.pages.wheretostay', compact('images', 'texts'));
    }
}
