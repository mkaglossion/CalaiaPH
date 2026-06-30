<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\StaySwiperImage;
use App\Models\StayText;
use App\Models\StaycationPageText;
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
use App\Models\PackageSwiperImage;
use App\Models\PackageText;
use App\Models\Package;
use App\Models\EventSection;
use App\Models\PackageImage;
use App\Models\PackageContent;

class AdminPagesController extends Controller
{
    private $pageConfigs = [
        'home' => [HomeSwiperImage::class, HomeText::class, 'admin.home.home'],
        'dayTour' => [DayTourSwiperImage::class, DayTourText::class, 'admin.dayTour.daytour'],
        'packages' => [PackageSwiperImage::class, PackageText::class, 'admin.packages.packagesInfos'],
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

        if ($method === 'items') {
            return $this->items();
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    private function showPage($page)
    {
        [$imageModel, $textModel, $view] = $this->pageConfigs[$page];
        $extraData = [];

        if ($page === 'dayTour') {
            $images = $imageModel::whereNull('daytour_id')->get();
        } elseif ($page === 'packages') {
            $images = $imageModel::all();
        } elseif ($page === 'foodAndBeverage') {
            // Only show hero swiper images (food_id is null), not Learn More modal images
            $images = $imageModel::whereNull('food_id')->get();
        } else {
            $images = $imageModel::all();
        }
        $texts = $textModel::orderByDesc('id')
            ->get()
            ->unique(function ($item) {
                return strtolower(trim((string) ($item->label ?? $item->title ?? '')));
            })
            ->reverse()
            ->values();
        $pageCopyTexts = [];

        if ($page === 'whatToDo') {
            $mainPackages = Package::orderBy('created_at', 'asc')->get();
            $mainPackagesTotal = Package::count();
            $packages = Package::orderBy('created_at', 'asc')->paginate(15);
            $extraData = compact('mainPackages', 'mainPackagesTotal', 'packages');
        } elseif ($page === 'whereToStay') {
            $pageCopyTexts = StaycationPageText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
                return [strtolower($field) => $content];
            })->toArray();
        }

        return view($view, compact('images', 'texts', 'pageCopyTexts') + $extraData);
    }

    public function items()
    {
        $sections = EventSection::orderBy('order')->get();
        return view('admin.home.items', compact('sections'));
    }
}
