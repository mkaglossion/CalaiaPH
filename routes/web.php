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
use App\Http\Controllers\Admin\DayTourSwiperImageController;
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
use App\Http\Controllers\Admin\AccommodationTextController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DayTourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\StaycationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmInfoController;
use App\Http\Controllers\Admin\StaycationAccommodationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PricingConfigController;
// ========== Root Route ==========
Route::get('/', function () {
    return redirect('/home');
});

// ========== Public Routes ==========
Route::get('/home', [PagesController::class, 'home'])->name('home');
Route::get('/daytour', [DayTourController::class, 'index'])->name('daytour');
Route::get('/staycation', [PagesController::class, 'staycation'])->name('staycation');
Route::get('/activities', [PagesController::class, 'activities'])->name('activities');
Route::get('/dining', [PagesController::class, 'dining'])->name('dining');
Route::get('/groups', [PagesController::class, 'groups'])->name('groups');
Route::get('/special-events', [PagesController::class, 'specialEvents'])->name('special-events');
Route::get('/farm', [PagesController::class, 'farm'])->name('farm');
Route::get('/shop', [PagesController::class, 'shop'])->name('shop');
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

// ========== Cart Routes ==========
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



// ========== Admin Routes ==========
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Main Sections
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
    // adminRoute('/bookings', AdminController::class, 'admin.bookings', 'bookings');
    // adminRoute('/staycations', AdminController::class, 'admin.staycations', 'staycations');
    // adminRoute('/activities', AdminController::class, 'admin.activities', 'activity');
    Route::prefix('/home')->group(function () {
        Route::get('/home', [AdminPagesController::class, 'home'])->name('admin.home.home');
    });
    Route::prefix('/day')->group(function () {
        Route::get('/day', [AdminController::class, 'dayTour'])->name('admin.day.day');
        Route::get('/day-tour', [AdminPagesController::class, 'dayTour'])->name('admin.day.daytour');
    });
    Route::prefix('/stay')->group(function () {
        Route::get('/where-to-stay', [AdminPagesController::class, 'whereToStay'])->name('admin.stay.wheretostay');
        Route::get('/staycations', [AdminController::class, 'staycation'])->name('admin.stay.staycations');
        Route::get('/accommodations', [AdminController::class, 'staycationAccommodation'])->name('admin.stay.accommodations');
    });
    Route::prefix('/do')->group(function () {
            Route::get('/activities', [AdminController::class, 'activity'])->name('admin.do.activities');
            Route::get('/what-to-do', [AdminPagesController::class, 'whatToDo'])->name('admin.do.whattodo');
    });
    Route::prefix('/food')->group(function () {
            Route::get('/food-and-beverage', [AdminPagesController::class, 'foodAndBeverage'])->name('admin.food.foodandbeverage');
            Route::get('/menus', [AdminController::class, 'menu'])->name('admin.food.menus');
    });
    Route::prefix('/events')->group(function () {
        Route::get('/events', [AdminController::class, 'event'])->name('admin.events.events');
    });
    Route::prefix('/special-events')->group(function () {
        Route::get('/', [AdminPagesController::class, 'specialEvents'])->name('admin.specialEvents.specialevents');
    });
    Route::prefix('/farm')->group(function () {
             Route::get('/farmInfos', [AdminController::class, 'farmInfo'])->name('admin.farm.farmInfos');
             Route::get('/our-farm', [AdminPagesController::class, 'ourFarm'])->name('admin.farm.ourfarm');
     });

     Route::prefix('/shop')->group(function () {
         Route::get('/shopstore', [AdminController::class, 'shopstore'])->name('admin.shop.shopstore');
         Route::get('/shopitems', [AdminController::class, 'shopitems'])->name('admin.shop.shopitems');
     });

     // ========== Pricing Configuration Routes ==========
    Route::prefix('/pricing')->group(function () {
        // Main config page
        Route::get('/config', [PricingConfigController::class, 'index'])->name('admin.pricingConfig.index');
        
        // Rate Matrix (Pricing Configs)
        Route::get('pricing-configs', [PricingConfigController::class, 'index'])->name('pricing-configs.index');
        Route::post('pricing-configs', [PricingConfigController::class, 'store'])->name('admin.pricing-configs.store');
        Route::put('pricing-configs/{pricingConfig}', [PricingConfigController::class, 'update'])->name('admin.pricing-configs.update');
        Route::delete('pricing-configs/{pricingConfig}', [PricingConfigController::class, 'destroy'])->name('admin.pricing-configs.destroy');

        // Weekend/Weekday Configuration
        Route::put('day-types', [PricingConfigController::class, 'updateDayTypes'])->name('admin.dayTypes.update');

        // Peak Periods (Durations)
        Route::post('peak-periods', [PricingConfigController::class, 'storePeakPeriod'])->name('admin.peakPeriods.store');
        Route::put('peak-periods/{peakPeriod}', [PricingConfigController::class, 'updatePeakPeriod'])->name('admin.peakPeriods.update');
        Route::delete('peak-periods/{peakPeriod}', [PricingConfigController::class, 'destroyPeakPeriod'])->name('admin.peak-periods.destroy');

        // Holidays
        Route::post('holidays', [PricingConfigController::class, 'storeHoliday'])->name('admin.holidays.store');
        Route::put('holidays/{holiday}', [PricingConfigController::class, 'updateHoliday'])->name('admin.holidays.update');
        Route::delete('holidays/{holiday}', [PricingConfigController::class, 'destroyHoliday'])->name('admin.holidays.destroy');

        // Price Calculator (API)
        Route::post('calculate-price', [PricingConfigController::class, 'calculatePrice'])->name('admin.pricing.calculate');

        // Seed Peak Types
        Route::get('seed-peak-types', [PricingConfigController::class, 'seedPeakTypes'])->name('admin.pricing.seedPeakTypes');

        // API Endpoints
        Route::get('api/peak-types', [PricingConfigController::class, 'getPeakTypes'])->name('api.pricing.peakTypes');
        Route::get('api/holidays', [PricingConfigController::class, 'getHolidays'])->name('api.pricing.holidays');
        Route::get('api/weekend-config', [PricingConfigController::class, 'getWeekendConfig'])->name('api.pricing.weekendConfig');
    });

    // ========== Admin CRUD Routes ==========

    // Text Controllers
    Route::resource('home-texts', HomeTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('day-tour-texts', DayTourTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('stay-texts', StayTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('do-texts', DoTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('food-texts', FoodTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('special-texts', SpecialTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('farm-texts', FarmTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('activity-text', ActivityTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('staycation-text', StaycationTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('menu-text', MenuTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('event-text', EventTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('farmInfo-text', FarmInfoTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('day-text', DayPackageTextController::class)->only(['store', 'update', 'destroy']);
    Route::post('/accommodations-text', [AccommodationTextController::class, 'store'])->name('accommodations-text.store');

    // Swiper Image Controllers
    Route::resource('swiper-images', SwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('day-tour-swiper-images', DayTourSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('stay-swiper-images', StaySwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('do-swiper-images', DoSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('food-swiper-images', FoodSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('special-swiper-images', SpecialSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('farm-swiper-images', FarmSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('activity-image', ActivitySwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('staycation-image', StaycationSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('menu-image', MenuSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('event-image', EventSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('farmInfo-image', FarmInfoSwiperImageController::class)->only(['store', 'destroy']);

    // List Controllers
    Route::resource('activity-list', ActivityController::class)->only(['store', 'destroy']);
    Route::resource('staycation-list', StaycationController::class)->only(['store', 'update', 'destroy']);
    Route::resource('menu-list', MenuController::class)->only(['store', 'destroy']);
    Route::resource('event-list', EventController::class)->only(['store', 'destroy']);
    Route::resource('farmInfo-list', FarmInfoController::class)->only(['store', 'destroy']);
    Route::resource('products', ProductController::class);

    // Other CRUD
    Route::resource('day-tours', DayTourController::class)->only(['store', 'destroy']);
    Route::resource('staycation-accommodations', StaycationAccommodationController::class)->only(['store', 'destroy']);



});
