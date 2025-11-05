<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SwiperImage;

class HomeController extends Controller
{
    public function index()
    {
        $images = SwiperImage::all();
        return view('admin.pages.home', compact('images'));
    }
}
