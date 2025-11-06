<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoSwiperImage;

class DoController extends Controller
{
    public function index()
    {
        $images = DoSwiperImage::all();
        return view('admin.pages.whattodo', compact('images'));
    }
}
