<?php

namespace App\Http\Controllers;

use App\Models\HomeSwiperImage;
use App\Models\HomeText;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $swiperImages = HomeSwiperImage::all();
        $texts = HomeText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        return view('home', compact('swiperImages', 'texts'));
    }
}