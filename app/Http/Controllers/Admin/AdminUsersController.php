<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminUsersController extends Controller
{
    public function index()
    {
        return view('admin.users');
    }
}
