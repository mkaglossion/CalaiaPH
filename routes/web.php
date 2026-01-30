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
use App\Http\Controllers\Admin\BookingSettingsController;
use App\Http\Controllers\Admin\InventoryController;

use App\Http\Controllers\Admin\HomeSwiperImageController;
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
use App\Http\Controllers\Admin\EventExperienceSectionController;
use App\Http\Controllers\Admin\TestimonialController;
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
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmInfoController;
use App\Http\Controllers\Admin\StaycationAccommodationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PricingConfigController;
use App\Http\Controllers\BookingCalendarController;
use App\Http\Controllers\CheckInOutController;
// ========== Root Route ==========
Route::get('/', function () {
    return redirect('/home');
});

// ========== Public Routes ==========
Route::get('/home', [PagesController::class, 'home'])->name('home');
Route::get('/daytour', [DayTourController::class, 'index'])->name('daytour');
Route::get('/day-tour-swiper-images/daytour/{id}', [App\Http\Controllers\Admin\DayTourSwiperImageController::class, 'getImages'])->name('public.day-tour-swiper-images.get');
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
Route::get('/booking/calendar', [BookingCalendarController::class, 'show'])->name('booking.calendar');
// Centralized API for activities
Route::get('/api/activities', [BookingController::class, 'getActivitiesApi'])->name('api.activities');
Route::get('/api/activity-images/{id}', function($id) {
    $activity = \App\Models\Activity::find($id);
    return $activity ? $activity->swiperImages->pluck('image_path') : [];
})->name('api.activity-images');
Route::get('/api/booking/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.activities');
Route::get('/api/booking/settings', [BookingController::class, 'getBookingSettings'])->name('api.booking.settings');
Route::get('/booking/activities', [BookingController::class, 'activities'])->name('booking.activities');
Route::post('/booking/activities', [BookingController::class, 'storeActivities'])->name('booking.storeActivities');
Route::get('/booking/staycation/activities', [BookingController::class, 'staycationActivities'])->name('booking.staycationActivities');
Route::post('/booking/staycation/activities', [BookingController::class, 'storeStaycationActivities'])->name('booking.storeStaycationActivities');
Route::get('/booking/staycation/payment', [BookingController::class, 'staycationPayment'])->name('booking.staycationPayment');
Route::match(['GET', 'POST'], '/booking/staycation', [BookingController::class, 'staycation'])->name('booking.staycation');
Route::get('/booking/staycation/availability', [BookingController::class, 'staycationAvailability'])->name('booking.staycationAvailability');
Route::get('/booking/staycation/food', [BookingController::class, 'staycationFood'])->name('booking.staycationFood');
Route::post('/booking/staycation/food/save', [BookingController::class, 'saveFoodSelection'])->name('booking.saveFoodSelection');
Route::get('/api/booking/staycation/availability', [BookingController::class, 'getStaycationAvailabilityApi'])->name('api.booking.staycationAvailability');
Route::get('/api/booking/staycation/prices', [BookingController::class, 'getStaycationPricesApi'])->name('api.booking.staycationPrices');
Route::get('/api/inventory/{roomId}', [\App\Http\Controllers\Admin\InventoryController::class, 'getInventory'])->name('api.inventory.get');
Route::post('/booking/select-dates', [BookingController::class, 'selectDates'])->name('booking.selectDates');
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.checkAvailability');
Route::get('/booking/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/payment', [BookingController::class, 'processPayment'])->name('booking.processPayment');
Route::get('/booking/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/widget', [BookingController::class, 'widget'])->name('booking.widget');
Route::post('/booking/calculate-price', [BookingController::class, 'getDynamicPrice'])->name('booking.calculatePrice');
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
Route::patch('/cart/{id}', [CartController::class, 'update'])->where('id', '.*')->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->where('id', '.*')->name('cart.remove');

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
Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/booking/history', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Fetch user's completed/cancelled bookings or past bookings for history
        $bookings = \App\Models\Booking::where('customer_email', $user->email)
            ->where(function($query) {
                $query->whereIn('status', ['completed', 'cancelled'])
                      ->orWhereRaw('DATE(checkout_date) < ?', [now()->toDateString()]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('booking_history', compact('bookings'));
    })->name('booking.history');

    Route::get('/checkinout', [CheckInOutController::class, 'index'])->name('checkinout');
    Route::post('/checkinout/checkin/{booking}', [CheckInOutController::class, 'checkin'])->name('checkinout.checkin');
    Route::post('/checkinout/checkout/{booking}', [CheckInOutController::class, 'checkout'])->name('checkinout.checkout');
    Route::get('/checkinout/next/{booking}', [CheckInOutController::class, 'nextAfterCheckin'])->name('checkinout.next');
});



// ========== Admin Routes ==========
Route::prefix('admin')->group(function () {
    // Main Sections
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
    
    // Bookings Management
    Route::prefix('bookings')->group(function () {
        Route::get('/', [AdminBookingsController::class, 'index'])->name('admin.bookings.index');
        Route::get('/inventory', [AdminBookingsController::class, 'inventory'])->name('admin.bookings.inventory');

        // Booking Extra Fees (mattress, pet fees) - must come before {id} route
        Route::get('/extra-fees', [BookingSettingsController::class, 'index'])->name('admin.booking-settings.index');
        Route::post('/extra-fees', [BookingSettingsController::class, 'update'])->name('admin.booking-settings.update');

        Route::get('/{id}', [AdminBookingsController::class, 'show'])->name('admin.bookings.show');
        Route::post('/{id}/cancel', [AdminBookingsController::class, 'cancel'])->name('admin.bookings.cancel');
    });
    
    // adminRoute('/staycations', AdminController::class, 'admin.staycations', 'staycations');
    // adminRoute('/activities', AdminController::class, 'admin.activities', 'activity');
    Route::prefix('/home')->group(function () {
        Route::get('/home', [AdminPagesController::class, 'home'])->name('admin.home.home');
    });
    Route::prefix('/day')->group(function () {
        Route::get('/day', [AdminController::class, 'dayTour'])->name('admin.day.day');
        Route::get('/day-tour', [AdminPagesController::class, 'dayTour'])->name('admin.day.daytour');
        Route::get('/day-tour/days', [AdminController::class, 'dayTourDays'])->name('admin.day-tour.days');
    });
    Route::prefix('/stay')->group(function () {
        Route::get('/where-to-stay', [AdminPagesController::class, 'whereToStay'])->name('admin.stay.wheretostay');
        Route::get('/staycations', [AdminController::class, 'staycation'])->name('admin.stay.staycations');
        Route::get('/accommodations', [AdminController::class, 'staycationAccommodation'])->name('admin.stay.accommodations');
        Route::get('/settings', [AdminController::class, 'staySettings'])->name('admin.stay.settings');
        // Removed redundant booking settings - now using centralized PricingConfig system
    });
    Route::prefix('/do')->group(function () {
            Route::get('/activities', [AdminController::class, 'activity'])->name('admin.do.activities');
            Route::get('/what-to-do', [AdminPagesController::class, 'whatToDo'])->name('admin.do.whattodo');
    });
    Route::prefix('/food')->group(function () {
            Route::get('/food-and-beverage', [AdminPagesController::class, 'foodAndBeverage'])->name('admin.food.foodandbeverage');
            Route::get('/menus', [AdminController::class, 'menu'])->name('admin.food.menus');
            Route::resource('foods', FoodController::class, ['names' => [
                'index' => 'admin.foods.index',
                'create' => 'admin.foods.create',
                'store' => 'admin.foods.store',
                'edit' => 'admin.foods.edit',
                'update' => 'admin.foods.update',
                'destroy' => 'admin.foods.destroy'
            ]]);
    });
    Route::prefix('/events')->group(function () {
        Route::get('/events', [AdminController::class, 'event'])->name('admin.events.events');
    });
    Route::prefix('/special-events')->group(function () {
        Route::get('/', [AdminPagesController::class, 'specialEvents'])->name('admin.specialEvents.specialevents');
    });
    Route::prefix('/event-experience')->group(function () {
        Route::get('/', [AdminController::class, 'eventExperience'])->name('admin.eventExperience.sections');
    });
    Route::prefix('/testimonials')->group(function () {
        Route::get('/', [AdminController::class, 'testimonials'])->name('admin.testimonials.index');
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

        // Booking Settings (mattress, pet fees) - moved to bookings section
        
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
    Route::resource('staycation-text', StaycationTextController::class, [
        'names' => [
            'store' => 'admin.staycation-text.store',
            'update' => 'admin.staycation-text.update',
            'destroy' => 'admin.staycation-text.destroy'
        ]
    ])->only(['store', 'update', 'destroy']);
    Route::resource('menu-text', MenuTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('event-text', EventTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('event-experience-sections', EventExperienceSectionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('testimonials', TestimonialController::class)->only(['store', 'update', 'destroy']);
    Route::resource('farmInfo-text', FarmInfoTextController::class)->only(['store', 'update', 'destroy']);
    Route::resource('day-text', DayPackageTextController::class)->only(['store', 'update', 'destroy']);
    Route::post('/accommodations-text', [AccommodationTextController::class, 'store'])->name('accommodations-text.store');

    // Swiper Image Controllers
    Route::resource('home-swiper-images', HomeSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('day-tour-swiper-images', DayTourSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::get('day-tour-swiper-images/daytour/{id}', [DayTourSwiperImageController::class, 'getImages'])->name('day-tour-swiper-images.get');
    Route::resource('stay-swiper-images', StaySwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('do-swiper-images', DoSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('food-swiper-images', FoodSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('special-swiper-images', SpecialSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('farm-swiper-images', FarmSwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('activity-image', ActivitySwiperImageController::class)->only(['store', 'update', 'destroy']);
    Route::resource('staycation-image', StaycationSwiperImageController::class, [
        'names' => [
            'store' => 'admin.staycation-image.store',
            'update' => 'admin.staycation-image.update',
            'destroy' => 'admin.staycation-image.destroy'
        ]
    ])->only(['store', 'update', 'destroy']);
    Route::resource('menu-image', MenuSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('event-image', EventSwiperImageController::class)->only(['store', 'destroy']);
    Route::resource('farmInfo-image', FarmInfoSwiperImageController::class)->only(['store', 'destroy']);

    // List Controllers
    Route::resource('activity-list', ActivityController::class)->only(['store', 'update', 'destroy']);
    Route::resource('staycation-list', StaycationController::class, [
        'names' => [
            'store' => 'admin.staycation-list.store',
            'update' => 'admin.staycation-list.update',
            'destroy' => 'admin.staycation-list.destroy'
        ]
    ])->only(['store', 'update', 'destroy']);
    Route::resource('menu-list', MenuController::class)->only(['store', 'destroy']);
    Route::resource('event-list', EventController::class)->only(['store', 'destroy']);
    Route::resource('farmInfo-list', FarmInfoController::class)->only(['store', 'destroy']);
    Route::resource('products', ProductController::class);

    // Other CRUD
    Route::resource('day-tours', DayTourController::class)->only(['store', 'update', 'destroy']);
    Route::resource('staycation-accommodations', StaycationAccommodationController::class)->only(['store', 'destroy']);

    // ========== Inventory Management Routes ==========
    Route::prefix('/inventory')->group(function () {
        Route::get('food', [InventoryController::class, 'food'])->name('admin.inventory.food');
        Route::get('shop', [InventoryController::class, 'shop'])->name('admin.inventory.shop');
        Route::post('store', [InventoryController::class, 'store'])->name('admin.inventory.store');
        Route::post('bulk', [InventoryController::class, 'bulk'])->name('admin.inventory.bulk');
        Route::post('quickset', [InventoryController::class, 'quickset'])->name('admin.inventory.quickset');
        Route::post('delete', [InventoryController::class, 'delete'])->name('admin.inventory.delete');
    });

});
