<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\StaySwiperImage;
use App\Models\StayText;
use App\Models\DoSwiperImage;
use App\Models\DoText;
use App\Models\FoodSwiperImage;
use App\Models\FoodText;
use App\Models\SpecialSwiperImage;
use App\Models\SpecialText;
use App\Models\FarmSwiperImage;
use App\Models\FarmText;
use App\Models\SwiperImage;
use App\Models\HomeText;

class AdminPagesController extends Controller
{
    private $pageConfigs = [
        'home' => [SwiperImage::class, HomeText::class, 'admin.pages.home'],
        'dayTour' => [DayTourSwiperImage::class, DayTourText::class, 'admin.pages.daytour'],
        'whereToStay' => [StaySwiperImage::class, StayText::class, 'admin.pages.wheretostay'],
        'whatToDo' => [DoSwiperImage::class, DoText::class, 'admin.pages.whattodo'],
        'foodAndBeverage' => [FoodSwiperImage::class, FoodText::class, 'admin.pages.foodandbeverage'],
        'specialEvents' => [SpecialSwiperImage::class, SpecialText::class, 'admin.pages.specialevents'],
        'ourFarm' => [FarmSwiperImage::class, FarmText::class, 'admin.pages.ourfarm'],
    ];

    public function __call($method, $args)
    {
        if (isset($this->pageConfigs[$method])) {
            return $this->showPage($method);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    private function showPage($page)
    {
        [$imageModel, $textModel, $view] = $this->pageConfigs[$page];
        $images = $imageModel::all();
        $texts = $textModel::all();

        return view($view, compact('images', 'texts'));
    }
}
