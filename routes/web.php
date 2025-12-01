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
use App\Http\Controllers\Admin\DayPackageTextController;


use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DayTourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\StaycationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmInfoController;
use App\Http\Controllers\Admin\DayTourController as AdminDayTourController;
use App\Http\Controllers\CartController;

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
Route::get('/special-events', [PagesController::class, 'specialEvents'])->name('special-events');
Route::get('/farm', [PagesController::class, 'farm'])->name('farm');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/weather', [WeatherController::class, 'show'])->name('weather.show');

// Booking Routes
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
// Centralized API for activities
Route::get('/api/activities', [BookingController::class, 'getActivitiesApi'])->name('api.activities');
Route::get('/api/booking/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.activities');
Route::get('/booking/activities', [BookingController::class, 'activities'])->name('booking.activities');
Route::post('/booking/activities', [BookingController::class, 'storeActivities'])->name('booking.storeActivities');
Route::get('/booking/staycation/activities', [BookingController::class, 'staycationActivities'])->name('booking.staycationActivities');
Route::post('/booking/staycation/activities', [BookingController::class, 'storeStaycationActivities'])->name('booking.storeStaycationActivities');
Route::get('/booking/staycation/payment', [BookingController::class, 'staycationPayment'])->name('booking.staycationPayment');
Route::match(['GET', 'POST'], '/booking/staycation', [BookingController::class, 'staycation'])->name('booking.staycation');
Route::post('/booking/select-dates', [BookingController::class, 'selectDates'])->name('booking.selectDates');
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.checkAvailability');
Route::get('/booking/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/payment', [BookingController::class, 'processPayment'])->name('booking.processPayment');
Route::get('/booking/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/widget', [BookingController::class, 'widget'])->name('booking.widget');
Route::get('/debug-session', [BookingController::class, 'debugSession']);

Route::get('/booking/reset-inventory', function () {
    session()->forget('inventory');
    return redirect('/booking/widget')->with('success', 'Inventory reset!');
})->name('booking.reset-inventory');
// Day Visit Routes
Route::match(['GET', 'POST'], '/booking/dayvisit', [BookingController::class, 'dayvisit'])->name('booking.dayvisit');
Route::post('/booking/dayvisit/store', [BookingController::class, 'storeDayvisit'])->name('booking.storeDayvisit');
// Use centralized API for dayvisit activities too
Route::get('/api/booking/dayvisit/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.dayvisit.activities');
Route::get('/booking/dayvisit/activities', [BookingController::class, 'dayvisitActivities'])->name('booking.dayvisitActivities');
Route::post('/booking/dayvisit/activities', [BookingController::class, 'storeDayvisitActivities'])->name('booking.storeDayvisitActivities');
Route::get('/booking/dayvisit/payment', [BookingController::class, 'dayvisitPayment'])->name('booking.dayvisitPayment');

// Special Event Routes
Route::match(['GET', 'POST'], '/booking/special-event', [BookingController::class, 'specialEvent'])->name('booking.specialEvent');
Route::get('/booking/special-event/details', [BookingController::class, 'specialEventDetails'])->name('booking.specialEventDetails');
Route::post('/booking/special-event/store', [BookingController::class, 'storeSpecialEvent'])->name('booking.storeSpecialEvent');
Route::get('/api/booking/special-event/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.specialEvent.activities');
Route::get('/booking/special-event/activities', [BookingController::class, 'specialEventActivities'])->name('booking.specialEventActivities');
Route::post('/booking/special-event/activities', [BookingController::class, 'storeSpecialEventActivities'])->name('booking.storeSpecialEventActivities');
Route::get('/booking/special-event/payment', [BookingController::class, 'specialEventPayment'])->name('booking.specialEventPayment');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CartController::class, 'checkoutForm'])->name('checkout.form');
Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->name('checkout.submit');
Route::get('/checkout/confirmation', function () {
    return view('checkout_confirmation');
})->name('checkout.confirmation');

// ========== Auth Routes ==========
Route::post('/register-step-one', [RegisterController::class, 'registerStepOne']);
Route::get('/verify/{token}', [RegisterController::class, 'verify']);
Route::get('/set-credentials/{token}', [RegisterController::class, 'showSetCredentialsForm']);
Route::post('/set-credentials/{token}', [RegisterController::class, 'setCredentials']);
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

Route::get('/login', function () { return redirect('/'); })->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/booking/history', function () {
        // TODO: Implement booking history logic
        $bookings = []; // Fetch user's bookings from database
        return view('booking_history', compact('bookings'));
    })->name('booking.history');

    Route::get('/checkinout', function () {
        // TODO: Implement check-in/out logic
        $activeBookings = []; // Fetch user's active bookings that can be checked in/out
        return view('checkinout', compact('activeBookings'));
    })->name('checkinout');
});


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
    Route::get('/day', [AdminController::class, 'dayTour'])->name('admin.day');
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

    $swiperRoutes = [
        'day-tour-swiper-images' => DaytourSwiperImageController::class,
        'stay-swiper-images' => StaySwiperImageController::class,
        'do-swiper-images' => DoSwiperImageController::class,
        'food-swiper-images' => FoodSwiperImageController::class,
        'special-swiper-images' => SpecialSwiperImageController::class,
        'farm-swiper-images' => FarmSwiperImageController::class,
    ];
    foreach ($swiperRoutes as $route => $controller) {
        Route::resource($route, $controller)->only(['index', 'store', 'destroy']);
    }
    Route::resource('activity-swiper-images', ActivitySwiperImageController::class)->only(['store', 'destroy']);

    // Text CRUD
    $textRoutes = [
        'home-texts' => HomeTextController::class,
        'day-tour-texts' => DayTourTextController::class,
        'stay-texts' => StayTextController::class,
        'do-texts' => DoTextController::class,
        'food-texts' => FoodTextController::class,
        'special-texts' => SpecialTextController::class,
        'farm-texts' => FarmTextController::class,
    ];
    foreach ($textRoutes as $route => $controller) {
        Route::resource($route, $controller)->only(['index', 'store', 'update', 'destroy']);
    }
    Route::resource('activity-texts', ActivityTextController::class)->only(['store', 'update', 'destroy']);

    // activities CRUD
    // Activity CRUD
    Route::resource('activity-list', ActivityController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('activity-image', ActivitySwiperImageController::class)->only(['index', 'store', 'destroy']);
    Route::resource('activity-text', ActivityTextController::class)->only(['store', 'update', 'destroy']);

    // Other CRUD resources
    $crudRoutes = [
        'staycation-list' => StaycationController::class,
        'menu-list' => MenuController::class,
        'event-list' => EventController::class,
        'farmInfo-list' => FarmInfoController::class,
        'day-tours' => AdminDayTourController::class,
    ];
    foreach ($crudRoutes as $route => $controller) {
        Route::resource($route, $controller)->only(['index', 'store', 'update', 'destroy']);
    }

    $imageRoutes = [
        'staycation-image' => StaycationSwiperImageController::class,
        'menu-image' => MenuSwiperImageController::class,
        'event-image' => EventSwiperImageController::class,
        'farmInfo-image' => FarmInfoSwiperImageController::class,
    ];
    foreach ($imageRoutes as $route => $controller) {
        Route::resource($route, $controller)->only(['index', 'store', 'destroy']);
    }

    $textRoutes2 = [
        'staycation-text' => StaycationTextController::class,
        'menu-text' => MenuTextController::class,
        'event-text' => EventTextController::class,
        'farmInfo-text' => FarmInfoTextController::class,
        'day-text' => DayPackageTextController::class,
    ];
    foreach ($textRoutes2 as $route => $controller) {
        Route::resource($route, $controller)->only(['store', 'update', 'destroy']);
    }

});
