<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SwiperImage;
use App\Models\HomeText;

class HomeController extends Controller
{
    public function index()
    {
        $images = SwiperImage::all();
        $texts = HomeText::all();
        return view('admin.pages.home', compact('images', 'texts'));
    }
}
