<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports');
    }
}
