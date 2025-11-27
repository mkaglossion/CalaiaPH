<?php

namespace App\Http\Controllers;

use App\Models\SwiperImage;
use App\Models\DayTourSwiperImage;
use App\Models\DoSwiperImage;
use App\Models\FarmSwiperImage;
use App\Models\FoodSwiperImage;
use App\Models\SpecialSwiperImage;
use App\Models\StaySwiperImage;
use App\Models\HomeText;
use App\Models\DayTourText;
use App\Models\StayText;
use App\Models\DoText;
use App\Models\FoodText;
use App\Models\SpecialText;
use App\Models\FarmText;

use App\Models\MenuText;
use App\Models\Event;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function home()
    {
        $swiperImages = SwiperImage::all();
        $texts = HomeText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'home';
        return view('home', compact('swiperImages', 'texts', 'active'));
    }

    public function daytour()
    {
        $swiperImages = DayTourSwiperImage::all();
        $texts = DayTourText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'daytour';
        return view('daytour', compact('swiperImages', 'texts', 'active'));
    }

    public function staycation()
    {
        $swiperImages = StaySwiperImage::all();
        $texts = StayText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'staycation';
        return view('staycation', compact('swiperImages', 'texts', 'active'));
    }

    public function activities()
    {
        $swiperImages = DoSwiperImage::all();
        $texts = DoText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'activities';
        return view('activities', compact('swiperImages', 'texts', 'active'));
    }

    public function dining()
    {
        $swiperImages = FoodSwiperImage::all();
        $texts = FoodText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'dining';
        return view('dining', compact('swiperImages', 'texts', 'active'));
    }

    public function groups()
    {
        $swiperImages = SpecialSwiperImage::all();
        $texts = SpecialText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'groups';
        return view('groups', compact('swiperImages', 'texts', 'active'));
    }

    public function specialEvents()
    {
        $swiperImages = SpecialSwiperImage::all();
        $texts = SpecialText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $events = Event::with(['swiperImages', 'texts'])->get();
        $active = 'special-events';
        return view('special-events', compact('swiperImages', 'texts', 'events', 'active'));
    }

    public function farm()
    {
        $swiperImages = FarmSwiperImage::all();
        $texts = FarmText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($text, $label) {
            return [strtolower($label) => $text];
        })->toArray();
        $active = 'farm';
        return view('farm', compact('swiperImages', 'texts', 'active'));
    }

    public function stay()
    {
        $swiperImages = StaySwiperImage::all();
        $texts = StayText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
            return [strtolower($title) => $content];
        })->toArray();
        $active = 'stay';
        return view('stay', compact('swiperImages', 'texts', 'active'));
    }

    public function food()
    {
        $swiperImages = FoodSwiperImage::all();
        $texts = FoodText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
            return [strtolower($title) => $content];
        })->toArray();
        $active = 'food';
        return view('food', compact('swiperImages', 'texts', 'active'));
    }
}