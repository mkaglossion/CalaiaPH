<?php

namespace App\Http\Controllers;

use App\Models\HomeSwiperImage;
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

use App\Models\Menu;
use App\Models\MenuText;
use App\Models\Event;
use App\Models\DayTour;
use App\Models\Product;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Generic method to render pages with swiper images and texts
     */
    protected function renderPage($swiperModel, $textModel, $viewName, $activeName, $textField = 'label', $contentField = 'text', $extraData = [])
    {
        $swiperImages = $swiperModel::all();
        $texts = $textModel::whereNotNull($textField)->pluck($contentField, $textField)->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $data = compact('swiperImages', 'texts') + ['active' => $activeName] + $extraData;

        return view($viewName, $data);
    }

    public function home()
    {
        return $this->renderPage(HomeSwiperImage::class, HomeText::class, 'home', 'home');
    }

    public function daytour()
    {
        $packages = DayTour::with(['swiperImages', 'texts', 'activities'])->get();
        return $this->renderPage(DayTourSwiperImage::class, DayTourText::class, 'daytour', 'daytour', 'label', 'text', compact('packages'));
    }

    public function staycation()
    {
        return $this->renderPage(StaySwiperImage::class, StayText::class, 'staycation', 'staycation');
    }

    public function activities()
    {
        return $this->renderPage(DoSwiperImage::class, DoText::class, 'activities', 'activities');
    }

    public function dining()
    {
        $menus = Menu::with('swiperImages')->get();
        return $this->renderPage(FoodSwiperImage::class, FoodText::class, 'dining', 'dining', 'label', 'text', compact('menus'));
    }

    public function groups()
    {
        return $this->renderPage(SpecialSwiperImage::class, SpecialText::class, 'groups', 'groups');
    }

    public function specialEvents()
    {
        $events = Event::with(['swiperImages', 'texts'])->get();
        return $this->renderPage(SpecialSwiperImage::class, SpecialText::class, 'special-events', 'special-events', 'label', 'text', compact('events'));
    }

    public function farm()
    {
        return $this->renderPage(FarmSwiperImage::class, FarmText::class, 'farm', 'farm');
    }

    public function shop()
    {
        return $this->renderPage(FarmSwiperImage::class, FarmText::class, 'shop', 'shop');
    }

    public function stay()
    {
        return $this->renderPage(StaySwiperImage::class, StayText::class, 'stay', 'stay');
    }

    public function food()
    {
        return $this->renderPage(FoodSwiperImage::class, FoodText::class, 'food', 'food');
    }
}