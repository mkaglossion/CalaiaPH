<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminBookingsController;
use App\Http\Controllers\Admin\AdminStaycationsController;
use App\Http\Controllers\Admin\AdminActivitiesController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminPagesController;

use App\Http\Controllers\Admin\SwiperImageController; 
use App\Http\Controllers\Admin\DaytourSwiperImageController; 
use App\Http\Controllers\Admin\StaySwiperImageController; 
use App\Http\Controllers\Admin\DoSwiperImageController;
use App\Http\Controllers\Admin\FoodSwiperImageController;
use App\Http\Controllers\Admin\SpecialSwiperImageController;
use App\Http\Controllers\Admin\FarmSwiperImageController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\DaytourController;
use App\Http\Controllers\Admin\StayController;
use App\Http\Controllers\Admin\DoController;
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\SpecialController;
use App\Http\Controllers\Admin\FarmController;


// ========== Public Route ==========
Route::get('/', function () {
    return view('home');
});

// ========== Auth Routes ==========
Route::post('/register-step-one', [RegisterController::class, 'registerStepOne']);
Route::get('/verify/{token}', [RegisterController::class, 'verify']);
Route::get('/set-credentials/{token}', [RegisterController::class, 'showSetCredentialsForm']);
Route::post('/set-credentials/{token}', [RegisterController::class, 'setCredentials']);
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

// ========== Helper Function for Admin Auth ==========
function adminRoute($uri, $controller, $name) {
    Route::get($uri, function () use ($controller) {
        if (!Auth::check() || Auth::user()->user_type !== 'admin') {
            abort(403, 'Unauthorized access.');
        }
        return app($controller)->index();
    })->name($name);
}

// ========== Admin Routes ==========
Route::prefix('admin')->group(function () {
    // Main Sections
    adminRoute('/dashboard', AdminDashboardController::class, 'admin.dashboard');
    adminRoute('/users', AdminUsersController::class, 'admin.users');
    adminRoute('/bookings', AdminBookingsController::class, 'admin.bookings');
    adminRoute('/staycations', AdminStaycationsController::class, 'admin.staycations');
    adminRoute('/activities', AdminActivitiesController::class, 'admin.activities');
    adminRoute('/reports', AdminReportsController::class, 'admin.reports');

    // Pages Submenu
    Route::prefix('/pages')->group(function () {
        // 🏠 Admin Home Page with Swiper CRUD
        Route::get('/home', [HomeController::class, 'index'])->name('admin.pages.home');
        Route::get('/day-tour', [DaytourController::class, 'index'])->name('admin.pages.daytour');
        Route::get('/where-to-stay', [StayController::class, 'index'])->name('admin.pages.wheretostay');
        Route::get('/what-to-do', [DoController::class, 'index'])->name('admin.pages.whattodo');
        Route::get('/food-and-beverage', [FoodController::class, 'index'])->name('admin.pages.foodandbeverage');
        Route::get('/special-events', [SpecialController::class, 'index'])->name('admin.pages.specialevents');
        Route::get('/our-farm', [FarmController::class, 'index'])->name('admin.pages.ourfarm');

    });

    // 🖼️ Swiper Images CRUD
    Route::resource('swiper-images', SwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('day-tour-swiper-images', DaytourSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('stay-swiper-images', StaySwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('do-swiper-images', DoSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('food-swiper-images', FoodSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('special-swiper-images', SpecialSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('farm-swiper-images', FarmSwiperImageController::class)->only(['index', 'store', 'destroy']);

});
