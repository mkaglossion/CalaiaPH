<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSwiperImage;
use App\Models\HomeText;

class HomeController extends Controller
{
    public function index()
    {
        $images = HomeSwiperImage::all();
        $texts = HomeText::all();
        return view('admin.pages.home', compact('images', 'texts'));
    }
}
