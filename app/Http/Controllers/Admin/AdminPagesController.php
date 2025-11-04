<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminPagesController extends Controller
{
    public function home()         { return view('admin.pages.home'); }
    public function dayTour()      { return view('admin.pages.daytour'); }
    public function whereToStay()  { return view('admin.pages.wheretostay'); }
    public function whatToDo()     { return view('admin.pages.whattodo'); }
    public function foodAndBeverage() { return view('admin.pages.foodandbeverage'); }
    public function specialEvents(){ return view('admin.pages.specialevents'); }
    public function ourFarm()      { return view('admin.pages.ourfarm'); }
}
