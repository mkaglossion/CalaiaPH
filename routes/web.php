<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\WeatherController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminBookingsController;
use App\Http\Controllers\Admin\AdminStaycationsController;
use App\Http\Controllers\Admin\AdminActivitiesController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\Admin\AdminController;

use App\Http\Controllers\Admin\SwiperImageController;
use App\Http\Controllers\Admin\DaytourSwiperImageController;
use App\Http\Controllers\Admin\StaySwiperImageController;
use App\Http\Controllers\Admin\DoSwiperImageController;
use App\Http\Controllers\Admin\FoodSwiperImageController;
use App\Http\Controllers\Admin\SpecialSwiperImageController;
use App\Http\Controllers\Admin\FarmSwiperImageController;
use App\Http\Controllers\Admin\ActivitySwiperImageController;
use App\Http\Controllers\Admin\StaycationSwiperImageController;
use App\Http\Controllers\Admin\MenuSwiperImageController;
use App\Http\Controllers\Admin\EventSwiperImageController;
use App\Http\Controllers\Admin\FarmInfoSwiperImageController;

use App\Http\Controllers\Admin\HomeTextController;
use App\Http\Controllers\Admin\DayTourTextController;
use App\Http\Controllers\Admin\StayTextController;
use App\Http\Controllers\Admin\DoTextController;
use App\Http\Controllers\Admin\FoodTextController;
use App\Http\Controllers\Admin\SpecialTextController;
use App\Http\Controllers\Admin\FarmTextController;
use App\Http\Controllers\Admin\ActivityTextController;
use App\Http\Controllers\Admin\StaycationTextController;
use App\Http\Controllers\Admin\MenuTextController;
use App\Http\Controllers\Admin\EventTextController;
use App\Http\Controllers\Admin\FarmInfoTextController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DayTourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\StaycationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmInfoController;

// ========== Root Route ==========
Route::get('/', function () {
    return redirect('/home');
});

// ========== Public Route ==========
Route::get('/home', [PagesController::class, 'home'])->name('home');
Route::get('/daytour', [DayTourController::class, 'index'])->name('daytour');
Route::get('/staycation', [PagesController::class, 'staycation'])->name('staycation');
Route::get('/activities', [PagesController::class, 'activities'])->name('activities');
Route::get('/dining', [PagesController::class, 'dining'])->name('dining');
Route::get('/groups', [PagesController::class, 'groups'])->name('groups');
Route::get('/farm', [PagesController::class, 'farm'])->name('farm');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/weather', [WeatherController::class, 'show'])->name('weather.show');

// Booking Routes
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::match(['GET', 'POST'], '/booking/staycation', [BookingController::class, 'staycation'])->name('booking.staycation');
Route::post('/booking/select-dates', [BookingController::class, 'selectDates'])->name('booking.selectDates');
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.checkAvailability');
Route::get('/booking/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/process-payment', [BookingController::class, 'processPayment'])->name('booking.processPayment');
Route::get('/booking/widget', [BookingController::class, 'widget'])->name('booking.widget');
Route::get('/booking/reset-inventory', function () {
    session()->forget('inventory');
    return redirect('/booking/widget')->with('success', 'Inventory reset!');
})->name('booking.reset-inventory');

// ========== Auth Routes ==========
Route::post('/register-step-one', [RegisterController::class, 'registerStepOne']);
Route::get('/verify/{token}', [RegisterController::class, 'verify']);
Route::get('/set-credentials/{token}', [RegisterController::class, 'showSetCredentialsForm']);
Route::post('/set-credentials/{token}', [RegisterController::class, 'setCredentials']);
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');


// // ========== Helper Function for Admin Auth ==========
// function adminRoute($uri, $controller, $name, $method = 'index') {
//     Route::get($uri, function () use ($controller, $method) {
//         if (!Auth::check() || Auth::user()->user_type !== 'admin') {
//             abort(403, 'Unauthorized access.');
//         }
//         return app($controller)->$method();
//     })->name($name);
// }
// ========== Admin Routes ==========
Route::prefix('admin')->group(function () {
    // Main Sections
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
    // adminRoute('/bookings', AdminController::class, 'admin.bookings', 'bookings');
    // adminRoute('/staycations', AdminController::class, 'admin.staycations', 'staycations');
    // adminRoute('/activities', AdminController::class, 'admin.activities', 'activity');
    Route::get('/activities', [AdminController::class, 'activity'])->name('admin.activities');
    Route::get('/staycations', [AdminController::class, 'staycation'])->name('admin.staycations');
    Route::get('/menus', [AdminController::class, 'menu'])->name('admin.menus');
    Route::get('/events', [AdminController::class, 'event'])->name('admin.events');
    Route::get('/farmInfos', [AdminController::class, 'farmInfo'])->name('admin.farmInfos');
    // adminRoute('/reports', AdminController::class, 'admin.reports', 'reports');

    // Pages Submenu
    Route::prefix('/pages')->group(function () {
        // 🏠 Admin Home Page with Swiper CRUD
        Route::get('/home', [AdminPagesController::class, 'home'])->name('admin.pages.home');
        Route::get('/day-tour', [AdminPagesController::class, 'dayTour'])->name('admin.pages.daytour');
        Route::get('/where-to-stay', [AdminPagesController::class, 'whereToStay'])->name('admin.pages.wheretostay');
        Route::get('/what-to-do', [AdminPagesController::class, 'whatToDo'])->name('admin.pages.whattodo');
        Route::get('/food-and-beverage', [AdminPagesController::class, 'foodAndBeverage'])->name('admin.pages.foodandbeverage');
        Route::get('/special-events', [AdminPagesController::class, 'specialEvents'])->name('admin.pages.specialevents');
        Route::get('/our-farm', [AdminPagesController::class, 'ourFarm'])->name('admin.pages.ourfarm');
    });

    // 🖼️ Swiper Images CRUD
    Route::get('swiper-images', [SwiperImageController::class, 'index'])->name('swiper-images.index');
    Route::post('swiper-images', [SwiperImageController::class, 'store'])->name('swiper-images.store');
    Route::delete('swiper-images/{swiperImage}', [SwiperImageController::class, 'destroy'])->name('swiper-images.destroy');
    Route::resource('day-tour-swiper-images', DaytourSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('stay-swiper-images', StaySwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('do-swiper-images', DoSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('food-swiper-images', FoodSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('special-swiper-images', SpecialSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('farm-swiper-images', FarmSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('activity-swiper-images', ActivitySwiperImageController::class)->only(['store', 'destroy']);

    // Text CRUD
    Route::resource('home-texts', HomeTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('day-tour-texts', DayTourTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('stay-texts', StayTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('do-texts', DoTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('food-texts', FoodTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('special-texts', SpecialTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('farm-texts', FarmTextController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('activity-texts', ActivityTextController::class)->only(['store', 'update', 'destroy']);

    // activities CRUD
    Route::resource('activity-list', ActivityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('activity-image', ActivitySwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('activity-text', ActivityTextController::class)->only(['store', 'update', 'destroy']);

    // staycation CRUD
    Route::resource('staycation-list', StaycationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('staycation-image', StaycationSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('staycation-text', StaycationTextController::class)->only(['store', 'update', 'destroy']);

    // menu CRUD
    Route::resource('menu-list', MenuController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('menu-image', MenuSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('menu-text', MenuTextController::class)->only(['store', 'update', 'destroy']);

    // event CRUD
    Route::resource('event-list', EventController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('event-image', EventSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('event-text', EventTextController::class)->only(['store', 'update', 'destroy']);

    // farmInfo CRUD
    Route::resource('farmInfo-list', FarmInfoController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('farmInfo-image', FarmInfoSwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('farmInfo-text', FarmInfoTextController::class)->only(['store', 'update', 'destroy']);
});
