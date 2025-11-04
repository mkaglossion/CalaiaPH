<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminBookingsController extends Controller
{
    public function index()
    {
        return view('admin.bookings');
    }
}
