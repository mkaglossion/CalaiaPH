<?php

namespace App\Http\Controllers;

use App\Models\SwiperImage;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $images = SwiperImage::all();
        return view('home', compact('images'));
    }
}