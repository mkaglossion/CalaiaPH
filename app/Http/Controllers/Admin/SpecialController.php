<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialSwiperImage;

class SpecialController extends Controller
{
    public function index()
    {
        $images = SpecialSwiperImage::all();
        return view('admin.pages.specialevents', compact('images'));
    }
}
