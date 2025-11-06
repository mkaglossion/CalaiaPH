<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmSwiperImage;

class FarmController extends Controller
{
    public function index()
    {
        $images = FarmSwiperImage::all();
        return view('admin.pages.ourfarm', compact('images'));
    }
}
