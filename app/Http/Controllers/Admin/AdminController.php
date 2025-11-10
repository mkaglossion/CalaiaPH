<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivitySwiperImage;
use App\Models\ActivityText;


class AdminController extends Controller
{
    public function activity()
    {
        $activities = Activity::all();
        $images = ActivitySwiperImage::all();
        $texts = ActivityText::all();
        return view('admin.activities', compact('activities', 'images', 'texts'));
    }
}
