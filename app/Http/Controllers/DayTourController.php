<?php

namespace App\Http\Controllers;

use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use Illuminate\Http\Request;

class DayTourController extends Controller
{
    public function index()
    {
        $swiperImages = DayTourSwiperImage::all();
        $texts = DayTourText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
            return [strtolower($title) => $content];
        })->toArray();

        $packages = config('daytour.packages');

        return view('daytour', compact('packages', 'swiperImages', 'texts'));
    }
}