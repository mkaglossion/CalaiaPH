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
use App\Models\HomeSwiperImage;
use App\Models\HomeText;

class AdminPagesController extends Controller
{
    private $pageConfigs = [
        'home' => [HomeSwiperImage::class, HomeText::class, 'admin.home.home'],
        'dayTour' => [DayTourSwiperImage::class, DayTourText::class, 'admin.dayTour.daytour'],
        'whereToStay' => [StaySwiperImage::class, StayText::class, 'admin.whereToStay.wheretostay'],
        'whatToDo' => [DoSwiperImage::class, DoText::class, 'admin.whatToDo.whattodo'],
        'foodAndBeverage' => [FoodSwiperImage::class, FoodText::class, 'admin.foodBeverages.foodandbeverage'],
        'specialEvents' => [SpecialSwiperImage::class, SpecialText::class, 'admin.specialEvents.specialevents'],
        'ourFarm' => [FarmSwiperImage::class, FarmText::class, 'admin.ourFarm.ourfarm'],
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
        if ($page === 'dayTour') {
            $images = $imageModel::whereNull('daytour_id')->get();
        } else {
            $images = $imageModel::all();
        }
        $texts = $textModel::all();

        return view($view, compact('images', 'texts'));
    }
}
