<?php

namespace App\Http\Controllers;

use App\Models\SwiperImage;
use App\Models\HomeText;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $swiperImages = SwiperImage::all();
        $texts = HomeText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        return view('home', compact('swiperImages', 'texts'));
    }
}