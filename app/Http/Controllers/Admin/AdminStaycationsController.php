<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminStaycationsController extends Controller
{
    public function index()
    {
        return view('admin.staycations');
    }
}
