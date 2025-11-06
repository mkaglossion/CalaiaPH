<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodSwiperImage;

class FoodController extends Controller
{
    public function index()
    {
        $images = FoodSwiperImage::all();
        return view('admin.pages.foodandbeverage', compact('images'));
    }
}
