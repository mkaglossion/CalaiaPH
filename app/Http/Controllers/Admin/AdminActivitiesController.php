<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminActivitiesController extends Controller
{
    public function index()
    {
        return view('admin.activities');
    }
}
