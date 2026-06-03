<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\DiningReservationController;
use App\Http\Controllers\WeatherController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminBookingsController;
use App\Http\Controllers\Admin\AdminStaycationsController;
use App\Http\Controllers\Admin\AdminActivitiesController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\PromoCodesController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPackageBookingsController;
use App\Http\Controllers\Admin\BookingSettingsController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryManagementController;
use App\Http\Controllers\Admin\DayTourInventoryController;

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
use App\Http\Controllers\Admin\PackageImageController;
use App\Http\Controllers\Admin\PackageSwiperImageController;
use App\Http\Controllers\Admin\ShopSwiperImageController;
use App\Http\Controllers\Admin\PackageController;use App\Http\Controllers\Admin\PackageInventoryController;
use App\Http\Controllers\Admin\HomeTextController;
use App\Http\Controllers\Admin\DayTourTextController;
use App\Http\Controllers\Admin\StayTextController;
use App\Http\Controllers\Admin\DoTextController;
use App\Http\Controllers\Admin\FoodTextController;
use App\Http\Controllers\Admin\SpecialTextController;
use App\Http\Controllers\Admin\PackageTextController;
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
use App\Http\Controllers\Admin\DiningCategoryImageController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSystemSettingsController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DayTourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\StaycationController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\IdVerificationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\FoodPackageController;
use App\Http\Controllers\Admin\ActivityPackageController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FarmInfoController;
use App\Http\Controllers\Admin\FarmExperiencesController;
use App\Http\Controllers\Admin\StaycationAccommodationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PricingConfigController;
use App\Http\Controllers\BookingCalendarController;
use App\Http\Controllers\CheckInOutController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\DayVisitController;
use App\Http\Controllers\PaymentController;
// ========== Root Route ==========

// Debug/test routes are local-only to avoid production exposure.
if (app()->environment('local')) {
    // Debug route to check maintenance mode cache
    Route::get('/debug/maintenance', function () {
        $settings = cache()->get('admin.settings', []);
        return response()->json([
            'cache_settings' => $settings,
            'is_maintenance_active' => $settings['system_maintenance'] ?? false,
            'cache_exists' => cache()->has('admin.settings'),
        ]);
    });
}

// Admin login (separate page) - allowed during maintenance via middleware except rules
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

Route::get('/', function () {
    return redirect('/home');
});

// ========== Public Routes ==========
Route::get('/home', [PagesController::class, 'home'])->name('home');
if (app()->environment('local')) {
    Route::get('/settings-test', function() { return view('settings-test'); })->name('settings.test');
}
Route::get('/our-story', [PagesController::class, 'ourStory'])->name('our-story');
Route::get('/sustainability', [PagesController::class, 'sustainability'])->name('sustainability');
Route::get('/our-services', [PagesController::class, 'ourServices'])->name('our-services');
Route::get('/daytour', [DayTourController::class, 'index'])->name('daytour');
Route::get('/day-tour-swiper-images/daytour/{id}', [App\Http\Controllers\Admin\DayTourSwiperImageController::class, 'getImages'])->name('public.day-tour-swiper-images.get');
Route::get('/packages', [PagesController::class, 'packages'])->name('packages');
Route::get('/staycation', [PagesController::class, 'staycation'])->name('staycation');
Route::get('/activities', [PagesController::class, 'activities'])->name('activities');
Route::get('/dining', [PagesController::class, 'dining'])->name('dining');
Route::post('/dining/reserve', [DiningReservationController::class, 'store'])->name('dining.reserve');
Route::post('/dining/prefill', [DiningReservationController::class, 'prefillBooking'])->name('dining.prefill');
Route::get('/dining-reservation', [DiningReservationController::class, 'dayVisitForm'])->name('dining-reservation.form');
Route::post('/dining-reservation/store', [DiningReservationController::class, 'storeDayVisit'])->name('dining-reservation.store');
Route::get('/groups', [PagesController::class, 'groups'])->name('groups');
Route::get('/special-events', [PagesController::class, 'specialEvents'])->name('special-events');
Route::get('/farm', [PagesController::class, 'farm'])->name('farm');
Route::get('/shop', [PagesController::class, 'shop'])->name('shop');
Route::get('/contact', [PagesController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/privacy-notice', [PagesController::class, 'privacyNotice'])->name('privacy-notice');
Route::get('/calaia-policy', [PagesController::class, 'calaiaPolicy'])->name('calaia-policy');
Route::get('/cookies-policy', [PagesController::class, 'cookiesPolicy'])->name('cookies-policy');
Route::get('/terms-service', [PagesController::class, 'termsService'])->name('terms-service');

Route::get('/weather', [WeatherController::class, 'show'])->name('weather.show');

// Booking Routes
Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/calendar', [BookingCalendarController::class, 'show'])->name('booking.calendar');
// Centralized API for activities - protected with rate limiting and security
// General API routes (60 req/min)
Route::middleware(['api.throttle:api', 'api.security'])->group(function () {
    Route::get('/api/activities', [BookingController::class, 'getActivitiesApi'])->name('api.activities');
    Route::get('/api/activity-images/{id}', function($id) {
        $activity = \App\Models\Activity::find($id);
        return $activity ? $activity->swiperImages->pluck('image_path') : [];
    })->name('api.activity-images');
    Route::get('/api/booking/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.activities');

    // API Routes for inventory (public access for preview purposes)
    Route::get('/api/inventory/{roomId}', [\App\Http\Controllers\Admin\InventoryController::class, 'getInventory'])->name('api.inventory.get');
    Route::get('/api/day-tour-inventory/{dayTourId}', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getInventory'])->name('api.day-tour-inventory.get');

    // Pricing API routes
    Route::get('api/pricing/peak-types', [PricingConfigController::class, 'getPeakTypes'])->name('api.pricing.peakTypes');
    Route::get('api/pricing/holidays', [PricingConfigController::class, 'getHolidays'])->name('api.pricing.holidays');
    Route::get('api/pricing/weekend-config', [PricingConfigController::class, 'getWeekendConfig'])->name('api.pricing.weekendConfig');

    // Real-time pricing endpoints for calendar
    Route::post('api/pricing/day-tour', [BookingController::class, 'calculateDayTourPrice'])->name('api.pricing.dayTour');
    Route::post('api/pricing/staycation', [BookingController::class, 'calculateStaycationPriceCalendar'])->name('api.pricing.staycationCalendar');

    // VAT rate configuration (public access for pricing calculations)
    Route::get('/api/vat-rate', function() {
        $vatService = app(\App\Services\VatService::class);
        return [
            'rate' => $vatService->getCurrentVatRate(),
            'rate_percentage' => $vatService->getCurrentVatRatePercentage(),
            'label' => $vatService->getCurrentVatRatePercentage() . '% VAT',
        ];
    })->name('api.vat-rate');
});

// Booking API routes (30 req/min) - stricter rate limiting for booking operations
Route::middleware(['api.throttle:booking', 'api.security'])->group(function () {
    Route::get('/api/booking/settings', [BookingController::class, 'getBookingSettings'])->name('api.booking.settings');
    Route::post('/api/booking/calculate-staycation-price', [BookingController::class, 'calculateStaycationPrice'])->name('api.booking.calculateStaycationPrice');
    Route::get('/api/booking/staycation/availability', [BookingController::class, 'getStaycationAvailabilityApi'])->name('api.booking.staycationAvailability');
    Route::get('/api/booking/staycation/prices', [BookingController::class, 'getStaycationPricesApi'])->name('api.booking.staycationPrices');

    // Day visit API routes
    Route::get('/api/booking/dayvisit/availability', [DayVisitController::class, 'getAvailabilityApi'])->name('api.dayvisit.availability');
    Route::get('/api/booking/dayvisit/activities', [DayVisitController::class, 'getActivitiesApi'])->name('api.booking.dayvisit.activities');
});
Route::get('/booking/activities', [BookingController::class, 'activities'])->name('booking.activities');
Route::post('/booking/activities', [BookingController::class, 'storeActivities'])->name('booking.storeActivities');
Route::get('/booking/staycation/activities', [BookingController::class, 'staycationActivities'])->name('booking.staycationActivities');
Route::post('/booking/staycation/activities', [BookingController::class, 'storeStaycationActivities'])->name('booking.storeStaycationActivities');
Route::get('/booking/staycation/payment', [BookingController::class, 'staycationPayment'])->name('booking.staycationPayment');
Route::get('/booking/payment/success', [BookingController::class, 'paymentSuccess'])->name('booking.payment.success');
Route::get('/booking/payment/failed', [BookingController::class, 'paymentFailed'])->name('booking.payment.failed');
Route::match(['GET', 'POST'], '/booking/staycation', [BookingController::class, 'staycation'])->name('booking.staycation');
Route::post('/booking/save-session', [BookingController::class, 'saveBookingSession'])->name('booking.saveSession');
Route::get('/booking/staycation/availability', [BookingController::class, 'staycationAvailability'])->name('booking.staycationAvailability');
// Endpoint: fetch external live site and return current glamping image
Route::get('/api/external/glamping-image', function() {
    $cacheKey = 'external.glamping_image';
    $image = cache()->remember($cacheKey, 30, function() {
        try {
            $resp = Http::timeout(10)->get('https://calaia.ph/booking/staycation/availability');
            if (!$resp->ok()) return null;
            $html = $resp->body();
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new \DOMXPath($doc);
            // Prefer exact alt, fallback to any image with 'glamping' in alt
            $node = $xpath->query("//img[@alt='Standard Glamping']")->item(0);
            if (!$node) {
                $nodes = $xpath->query("//img[contains(translate(@alt,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'glamping')]");
                $node = $nodes->item(0);
            }
            if ($node instanceof \DOMElement) {
                $src = $node->getAttribute('src');
                if (strpos($src, '//') === 0) $src = 'https:' . $src;
                elseif (!preg_match('#^https?://#', $src)) $src = 'https://calaia.ph' . (strpos($src, '/') === 0 ? '' : '/') . $src;
                return $src;
            }
        } catch (\Exception $e) {
            // ignore and return null
        }
        return null;
    });
    return response()->json(['image' => $image]);
});

// Internal endpoint: return current Glamping image from DB (swiperImages first, then image)
Route::get('/api/internal/glamping-image', function() {
    try {
        $staycation = \App\Models\Staycation::where('name', 'Standard Glamping')->first();
        $img = null;
        if ($staycation) {
            // Prefer explicit staycation.image (admin-set image) first
            if (!empty($staycation->image)) {
                $img = $staycation->image;
            } elseif ($staycation->swiperImages && $staycation->swiperImages->count() > 0) {
                $first = $staycation->swiperImages->first();
                $img = $first->image_url ?? $first->image_path ?? null;
            }
        }
        if ($img) {
            if (strpos($img, '//') === 0) $img = 'https:' . $img;
            elseif (!preg_match('#^https?://#', $img)) $img = url($img);
        }
        return response()->json(['image' => $img]);
    } catch (\Exception $e) {
        return response()->json(['image' => null], 500);
    }
});

// Internal endpoint: return current Camping image from DB (prefer staycation.image then swiperImages)
Route::get('/api/internal/camping-image', function() {
    try {
        // Prefer exact 'Car Camping', otherwise any staycation with 'camping' or 'byot' in name
        $staycation = \App\Models\Staycation::where('name', 'Car Camping')->first()
            ?? \App\Models\Staycation::whereRaw("LOWER(name) LIKE '%camping%'")->first()
            ?? \App\Models\Staycation::whereRaw("LOWER(name) LIKE '%byot%'")->first();

        $img = null;
        if ($staycation) {
            if (!empty($staycation->image)) {
                $img = $staycation->image;
            } elseif ($staycation->swiperImages && $staycation->swiperImages->count() > 0) {
                $first = $staycation->swiperImages->first();
                $img = $first->image_url ?? $first->image_path ?? null;
            }
        }
        if ($img) {
            if (strpos($img, '//') === 0) $img = 'https:' . $img;
            elseif (!preg_match('#^https?://#', $img)) $img = url($img);
        }
        return response()->json(['image' => $img]);
    } catch (\Exception $e) {
        return response()->json(['image' => null], 500);
    }
});
Route::get('/booking/staycation/food', [BookingController::class, 'staycationFood'])->name('booking.staycationFood');
Route::post('/booking/staycation/food/save', [BookingController::class, 'saveFoodSelection'])->name('booking.saveFoodSelection');
Route::get('/api/booking/staycation/availability', [BookingController::class, 'getStaycationAvailabilityApi'])->name('api.booking.staycationAvailability');
Route::get('/api/booking/staycation/prices', [BookingController::class, 'getStaycationPricesApi'])->name('api.booking.staycationPrices');
// API Routes for inventory (public access for preview purposes)
Route::get('/api/inventory/{roomId}', [\App\Http\Controllers\Admin\InventoryController::class, 'getInventory'])->name('api.inventory.get');
Route::get('/api/day-tour-inventory/{dayTourId}', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getInventory'])->name('api.day-tour-inventory.get');
if (app()->environment('local')) {
    Route::get('/test-inventory/{dayTourId}', function($dayTourId) {
        $controller = new \App\Http\Controllers\Admin\DayTourInventoryController(new \App\Services\DayTourInventoryService());
        return $controller->getInventory($dayTourId);
    });
}
Route::post('/booking/select-dates', [BookingController::class, 'selectDates'])->name('booking.selectDates');
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.checkAvailability');
Route::get('/booking/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/booking/create-payment-intent', [BookingController::class, 'createPaymentIntent'])->name('booking.createPaymentIntent');
Route::post('/booking/initiate-payment', [BookingController::class, 'initiatePayment'])->name('booking.initiatePayment');
Route::post('/booking/payment', [BookingController::class, 'processPayment'])->name('booking.processPayment');
if (app()->environment('local')) {
Route::get('/test-paymongo', function() {
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->post("https://api.paymongo.com/v1/checkout_sessions", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'data' => [
                    'attributes' => [
                        'amount' => 10000, // ₱100.00 in centavos
                        'currency' => 'PHP',
                        'description' => 'Test Payment',
                        'success_url' => url('/test-success'),
                        'cancel_url' => url('/test-failed'),
                    ]
                ]
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $checkoutUrl = $data['data']['attributes']['checkout_url'];

        return redirect($checkoutUrl);
    } catch (\Exception $e) {
        return "PayMongo Error: " . $e->getMessage();
    }
});

Route::get('/test-success', function() {
    return "Payment Success! Check your PayMongo dashboard.";
});

Route::get('/test-failed', function() {
    return "Payment Failed or Cancelled.";
});
}
Route::get('/booking/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/widget', [BookingController::class, 'widget'])->name('booking.widget');
Route::post('/booking/calculate-price', [BookingController::class, 'getDynamicPrice'])->name('booking.calculatePrice');
if (app()->environment('local')) {
    Route::get('/debug-pricing', function () {
        $configs = \App\Models\PricingConfig::where('unit_type', 'glamping')
            ->with(['peakType', 'dayType'])
            ->get();

        $data = [];
        foreach ($configs as $config) {
            $data[] = [
                'id' => $config->id,
                'unit_type' => $config->unit_type,
                'peak_type' => $config->peakType->name,
                'day_type' => $config->dayType->name,
                'price' => $config->price
            ];
        }

        return response()->json($data);
    });

    Route::get('/debug-session', [BookingController::class, 'debugSession']);

    Route::get('/booking/reset-inventory', function () {
        session()->forget('inventory');
        return redirect('/booking/widget')->with('success', 'Inventory reset!');
    })->name('booking.reset-inventory');
}
// Day Visit Routes
Route::get('/booking/dayvisit', [DayVisitController::class, 'index'])->name('dayvisit.index');
Route::match(['GET', 'POST'], '/booking/dayvisit/availability', [DayVisitController::class, 'availability'])->name('dayvisit.availability');
Route::post('/booking/dayvisit/storeAvailability', [DayVisitController::class, 'storeAvailability'])->name('dayvisit.storeAvailability');
Route::get('/api/booking/dayvisit/availability', [DayVisitController::class, 'getAvailabilityApi'])->name('api.dayvisit.availability');

// Use centralized API for dayvisit activities too
Route::get('/api/booking/dayvisit/activities', [DayVisitController::class, 'getActivitiesApi'])->name('api.booking.dayvisit.activities');
Route::get('/booking/dayvisit/activities', [DayVisitController::class, 'activities'])->name('dayvisit.activities');
Route::post('/booking/dayvisit/activities', [DayVisitController::class, 'storeActivities'])->name('dayvisit.storeActivities');
Route::get('/booking/dayvisit/payment', [DayVisitController::class, 'payment'])->name('dayvisit.payment');
Route::post('/booking/dayvisit/payment', [DayVisitController::class, 'processPayment'])->name('dayvisit.processPayment');
Route::post('/booking/dayvisit/initiate-payment', [DayVisitController::class, 'initiatePayment'])->name('dayvisit.initiatePayment');
Route::get('/booking/dayvisit/payment/success', [DayVisitController::class, 'paymentSuccess'])->name('dayvisit.paymentSuccess');
Route::get('/booking/dayvisit/payment/failed', [DayVisitController::class, 'paymentFailed'])->name('dayvisit.paymentFailed');

if (app()->environment('local')) {
    // Test route for payment page
    Route::get('/test-payment', function () {
        // Set dummy booking data
        Session::put('dayvisit_booking_data', [
            'booking_date' => now()->toDateString(),
            'guests' => 2,
            'price' => 500,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+639123456789'
        ]);

        // Set dummy selected activities
        Session::put('dayvisit_selected_activities', [
            [
                'name' => 'Farm Tour',
                'price' => 200,
                'guests' => 2,
                'id' => 1
            ],
            [
                'name' => 'Nature Hike',
                'price' => 300,
                'guests' => 2,
                'id' => 2
            ]
        ]);

        return redirect()->route('dayvisit.payment');
    });

    Route::get('/test-activities', function () {
        // Set dummy booking data
        Session::put('dayvisit_booking_data', [
            'booking_date' => now()->toDateString(),
            'guests' => 2,
            'price' => 500,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+639123456789'
        ]);

        return redirect()->route('dayvisit.activities');
    });
}

// ========== Cart Routes ==========
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{id}', [CartController::class, 'update'])->where('id', '.*')->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->where('id', '.*')->name('cart.remove');

Route::get('/checkout', [CartController::class, 'checkoutForm'])->name('checkout.form');
Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->name('checkout.submit');
Route::get('/checkout/success', [CartController::class, 'checkoutSuccess'])->name('checkout.success');
Route::get('/checkout/confirmation', function () {
    return view('checkout_confirmation');
})->name('checkout.confirmation');

Route::get('/checkout/failed', function () {
    return redirect()->route('checkout.form')->with('error', 'Payment failed or was cancelled. Please try again.');
})->name('checkout.failed');

// ========== Auth Routes ==========
Route::post('/register-step-one', [RegisterController::class, 'registerStepOne']);
Route::get('/verify/{token}', [RegisterController::class, 'verify']);
Route::get('/set-credentials/{token}', [RegisterController::class, 'showSetCredentialsForm']);
Route::post('/set-credentials/{token}', [RegisterController::class, 'setCredentials']);
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

Route::get('/login', function () { return redirect('/'); })->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/auth/google/redirect', [SocialLoginController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/facebook/redirect', [SocialLoginController::class, 'redirectToFacebook'])->name('auth.facebook.redirect');
Route::get('/auth/facebook/callback', [SocialLoginController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
Route::match(['GET', 'POST'], '/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('forgot.password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgot.password.post');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('reset.password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset.password.post');

Route::resource('testimonials', TestimonialController::class)->only(['create', 'store']);

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
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // Main Sections
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/reports', [AdminReportsController::class, 'index'])->name('admin.reports.index');
    Route::resource('users', AdminUsersController::class, ['names' => [
        'index' => 'admin.users.index',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]]);

    // Bookings Management
    Route::prefix('bookings')->group(function () {
        Route::get('/', [AdminBookingsController::class, 'index'])->name('admin.bookings.index');
        Route::get('/inventory', [InventoryManagementController::class, 'index'])->name('admin.bookings.inventory');
        Route::get('/paymongo-webhooks', [AdminBookingsController::class, 'paymongoWebhooks'])->name('admin.bookings.paymongo-webhooks');

        // Booking Extra Fees (mattress, pet fees) - must come before {id} route
        Route::get('/extra-fees', [BookingSettingsController::class, 'index'])->name('admin.booking-settings.index');
        Route::post('/extra-fees', [BookingSettingsController::class, 'update'])->name('admin.booking-settings.update');
        Route::post('/mobile-layout', [BookingSettingsController::class, 'updateMobileLayout'])->name('admin.booking-settings.mobile-layout.update');

        // Package bookings (admin)
        Route::get('/package/{id}', [AdminPackageBookingsController::class, 'show'])->name('admin.package_bookings.show');
        Route::post('/package/{id}/confirm', [AdminPackageBookingsController::class, 'confirm'])->name('admin.package_bookings.confirm');
        Route::post('/package/{id}/cancel', [AdminPackageBookingsController::class, 'cancel'])->name('admin.package_bookings.cancel');
        Route::post('/package/record-payment', [AdminPackageBookingsController::class, 'recordPayment'])->name('admin.package_bookings.record-payment');

        Route::get('/{id}', [AdminBookingsController::class, 'show'])->name('admin.bookings.show');
        Route::post('/{id}/cancel', [AdminBookingsController::class, 'cancel'])->name('admin.bookings.cancel');
    });

    // Reservation Monitor
    Route::prefix('reservations')->group(function () {
        Route::get('/monitor', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'index'])->name('admin.reservations.monitor');
        Route::get('/api/recent', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'getRecent'])->name('admin.reservations.recent');
        Route::get('/api/stats', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'getStats'])->name('admin.reservations.stats');
        Route::get('/booking/{id}', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'getBookingDetails'])->name('admin.reservations.booking-details');
        Route::get('/{id}/timeline', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'timeline'])->name('admin.reservations.timeline');
        Route::post('/record-payment', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'recordPayment'])->name('admin.reservations.record-payment');
        Route::post('/bulk-update', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'bulkUpdate'])->name('admin.reservations.bulk-update');
        Route::post('/export', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'export'])->name('admin.reservations.export');
        
        // More specific routes MUST come before generic {id} routes
        Route::put('/dining/{id}/status', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'updateDiningStatus'])->name('admin.reservations.update-dining-status');
        Route::put('/{id}/status', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'updateStatus'])->name('admin.reservations.update-status');
        
        Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\ReservationMonitorController::class, 'cancelBooking'])->name('admin.reservations.cancel');
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
        Route::get('/day-tour/inventory', function () { return redirect('/admin/bookings/inventory'); })->name('admin.day-tour.inventory');
        Route::post('/day-tour/inventory', [DayTourInventoryController::class, 'store'])->name('admin.day-tour.inventory.store');
        Route::put('/day-tour/inventory/{inventory}', [DayTourInventoryController::class, 'update'])->name('admin.day-tour.inventory.update');
        Route::delete('/day-tour/inventory/{inventory}', [DayTourInventoryController::class, 'delete'])->name('admin.day-tour.inventory.delete');
    });
    Route::prefix('/packages')->group(function () {
        // Hide direct package manager pages; use What to Do CMS section instead.
        Route::get('/', function () { return redirect()->route('admin.do.whattodo'); })->name('admin.packages.index');
        Route::get('/create', function () { return redirect()->route('admin.do.whattodo'); })->name('admin.packages.create');
        Route::post('/', [PackageController::class, 'store'])->name('admin.packages.store');
        Route::get('/{package}/edit', [PackageController::class, 'edit'])->name('admin.packages.edit');
        Route::put('/{package}', [PackageController::class, 'update'])->name('admin.packages.update');
        Route::delete('/{package}', [PackageController::class, 'destroy'])->name('admin.packages.destroy');
        
        Route::post('{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('admin.packages.toggle-status');
        
        // Inventory Routes (must come after other routes)
        Route::get('/inventory', function () { return redirect('/admin/bookings/inventory'); })->name('admin.packages.inventory');
        Route::post('/inventory', [PackageInventoryController::class, 'storeInventory'])->name('admin.packages.inventory.store');
        Route::post('/inventory/bulk-upsert', [PackageInventoryController::class, 'bulkUpsert'])->name('admin.packages.inventory.bulk-upsert');
        Route::delete('/inventory/bulk-delete', [PackageInventoryController::class, 'bulkDeleteByIds'])->name('admin.packages.inventory.bulk-delete');
        Route::put('/inventory/{inventory}', [PackageInventoryController::class, 'updateInventoryRecord'])->name('admin.packages.inventory.update');
        Route::delete('/inventory/{inventory}', [PackageInventoryController::class, 'destroyInventory'])->name('admin.packages.inventory.destroy');
        Route::get('/information', [AdminPagesController::class, 'packages'])->name('admin.packages.packagesInfos');
        Route::resource('swiper-images', PackageSwiperImageController::class, ['names' => [
            'store' => 'package-swiper-images.store',
            'update' => 'package-swiper-images.update',
            'destroy' => 'package-swiper-images.destroy'
        ]]);
        Route::resource('texts', PackageTextController::class, ['names' => [
            'store' => 'package-texts.store',
            'update' => 'package-texts.update',
            'destroy' => 'package-texts.destroy'
        ]]);
    });
    Route::prefix('/stay')->group(function () {
        Route::get('/where-to-stay', [AdminPagesController::class, 'whereToStay'])->name('admin.stay.wheretostay');
        Route::get('/staycations', [AdminController::class, 'staycation'])->name('admin.stay.staycations');
        Route::get('/accommodations', [AdminController::class, 'staycationAccommodation'])->name('admin.stay.accommodations');
        Route::get('/settings', [AdminController::class, 'staySettings'])->name('admin.stay.settings');
        Route::get('/inventory', function () { return redirect('/admin/bookings/inventory'); })->name('admin.stay.inventory');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('admin.stay.inventory.store');
        Route::post('/inventory/bulk-upsert', [InventoryController::class, 'bulkUpsert'])->name('admin.stay.inventory.bulk-upsert');
        Route::delete('/inventory/bulk-delete', [InventoryController::class, 'bulkDeleteByIds'])->name('admin.stay.inventory.bulk-delete');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('admin.stay.inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'delete'])->name('admin.stay.inventory.delete');
        // Removed redundant booking settings - now using centralized PricingConfig system
    });
    Route::prefix('/do')->group(function () {
            Route::get('/activities', [AdminController::class, 'activity'])->name('admin.do.activities');
            Route::get('/api/activity-packages', [ActivityPackageController::class, 'apiIndex'])->name('admin.activity-packages.api.index');
            Route::resource('activity-packages', ActivityPackageController::class, ['except' => ['create', 'index'], 'names' => [
                'edit' => 'admin.activity-packages.edit',
                'store' => 'admin.activity-packages.store',
                'show' => 'admin.activity-packages.show',
                'update' => 'admin.activity-packages.update',
                'destroy' => 'admin.activity-packages.destroy'
            ]]);
            Route::get('/what-to-do', [AdminPagesController::class, 'whatToDo'])->name('admin.do.whattodo');
    });
    Route::prefix('/food')->group(function () {
            Route::get('/food-and-beverage', [AdminPagesController::class, 'foodAndBeverage'])->name('admin.food.foodandbeverage');
            Route::get('/menus', [AdminController::class, 'menu'])->name('admin.food.menus');
            Route::resource('foods', FoodController::class, ['names' => [
                'index' => 'admin.foods.index',
                'create' => 'admin.foods.create',
                'store' => 'admin.foods.store',
                'show' => 'admin.foods.show',
                'edit' => 'admin.foods.edit',
                'update' => 'admin.foods.update',
                'destroy' => 'admin.foods.destroy'
            ]]);
            Route::get('/api/foods', [FoodController::class, 'apiIndex'])->name('admin.foods.api.index');
            Route::resource('packages', \App\Http\Controllers\Admin\FoodPackageController::class, ['except' => ['show'], 'names' => [
                'index' => 'admin.food.packages.index',
                'create' => 'admin.food.packages.create',
                'store' => 'admin.food.packages.store',
                'edit' => 'admin.food.packages.edit',
                'update' => 'admin.food.packages.update',
                'destroy' => 'admin.food.packages.destroy'
            ]]);
            Route::get('/api/packages', [\App\Http\Controllers\Admin\FoodPackageController::class, 'apiIndex'])->name('admin.food.packages.api.index');
            Route::get('/api/packages/{package}/details', [\App\Http\Controllers\Admin\FoodPackageController::class, 'getPackageDetails'])->name('admin.food.packages.details');
            Route::resource('category-images', DiningCategoryImageController::class, ['names' => [
                'index' => 'admin.dining.category-images.index',
                'store' => 'admin.dining.category-images.store',
                'destroy' => 'admin.dining.category-images.destroy'
            ]]);
    });
    Route::prefix('/events')->group(function () {
        Route::get('/events', [AdminController::class, 'event'])->name('admin.events.events');
    });
    Route::prefix('/special-events')->group(function () {
        Route::get('/', [AdminPagesController::class, 'specialEvents'])->name('admin.specialEvents.specialevents');
        Route::get('/manager', [AdminController::class, 'specialEventsManager'])->name('admin.specialEvents.manager');
    });
    Route::prefix('/event-experience')->group(function () {
        Route::get('/', [AdminController::class, 'eventExperience'])->name('admin.eventExperience.sections');
    });
    Route::prefix('/testimonials')->group(function () {
        Route::get('/', [AdminController::class, 'testimonials'])->name('admin.testimonials.index');
        Route::post('/', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
        Route::post('/{id}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
        Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');
    });
    Route::prefix('/farm')->group(function () {
             Route::get('/farmInfos', [AdminController::class, 'farmInfo'])->name('admin.farm.farmInfos');
             Route::get('/our-farm', [AdminPagesController::class, 'ourFarm'])->name('admin.farm.ourfarm');
             Route::get('/experiences', [FarmExperiencesController::class, 'index'])->name('admin.farm.experiences');
             Route::post('/experiences/save', [FarmExperiencesController::class, 'save'])->name('admin.farm.experiences.save');
             Route::post('/experiences/upload-image', [FarmExperiencesController::class, 'uploadImage'])->name('admin.farm.experiences.uploadImage');
     });

     Route::prefix('/shop')->group(function () {
         Route::get('/shopstore', [AdminController::class, 'shopstore'])->name('admin.shop.shopstore');
         Route::get('/shopitems', [AdminController::class, 'shopitems'])->name('admin.shop.shopitems');
         Route::get('/orders', [AdminController::class, 'shopOrders'])->name('admin.shop.orders');
         Route::resource('shop-swiper-images', ShopSwiperImageController::class, ['names' => [
             'store' => 'admin.shop-swiper-images.store',
             'update' => 'admin.shop-swiper-images.update',
             'destroy' => 'admin.shop-swiper-images.destroy'
         ]]);
         Route::post('shop-swiper-images/bulk-delete', [ShopSwiperImageController::class, 'bulkDelete'])->name('admin.shop-swiper-images.bulk-delete');
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

        // API Endpoints (admin context - different names to avoid conflicts)
        Route::get('api/peak-types', [PricingConfigController::class, 'getPeakTypes'])->name('admin.api.pricing.peakTypes');
        Route::get('api/holidays', [PricingConfigController::class, 'getHolidays'])->name('admin.api.pricing.holidays');
        Route::get('api/weekend-config', [PricingConfigController::class, 'getWeekendConfig'])->name('admin.api.pricing.weekendConfig');
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
    Route::resource('package-images', PackageImageController::class)->only(['store', 'update', 'destroy']);
    Route::get('package-images/package/{id}', [PackageImageController::class, 'getImages'])->name('package-images.get');
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
    Route::resource('activity-list', ActivityController::class)->only(['store', 'show', 'update', 'destroy']);
    Route::resource('staycation-list', StaycationController::class, [
        'names' => [
            'store' => 'admin.staycation-list.store',
            'show' => 'admin.staycation-list.show',
            'update' => 'admin.staycation-list.update',
            'destroy' => 'admin.staycation-list.destroy'
        ]
    ])->only(['store', 'show', 'update', 'destroy']);
    Route::resource('menu-list', MenuController::class)->only(['store', 'show', 'destroy']);
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

    // ========== Day Tour Inventory Management Routes ==========
    Route::prefix('/day-tour-inventory')->group(function () {
        Route::post('/quickset', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'quickset'])->name('admin.day-tour-inventory.quickset');
        Route::post('/store', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'store'])->name('admin.day-tour-inventory.store');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'delete'])->name('admin.day-tour-inventory.delete');
        Route::get('/{dayTourId}/calendar', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getCalendar'])->name('admin.day-tour-inventory.calendar');
        Route::post('/{dayTourId}/update', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'updateInventory'])->name('admin.day-tour-inventory.update');
        Route::post('/{dayTourId}/block', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'blockDate'])->name('admin.day-tour-inventory.block');
        Route::post('/{dayTourId}/unblock', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'unblockDate'])->name('admin.day-tour-inventory.unblock');
        Route::post('/{dayTourId}/bulk-update', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'bulkUpdate'])->name('admin.day-tour-inventory.bulk');
        Route::post('/{dayTourId}/initialize', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'initialize'])->name('admin.day-tour-inventory.initialize');
        Route::get('/availability/all', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getAllAvailability'])->name('admin.day-tour-inventory.all-availability');
    });

    // ========== Admin Settings & Profile Routes ==========
    Route::prefix('/settings')->group(function () {
        Route::get('/profile', [AdminSettingsController::class, 'edit'])->name('admin.settings.profile');
        Route::put('/profile', [AdminSettingsController::class, 'updateProfile'])->name('admin.settings.profile.update');
        Route::get('/password', [AdminSettingsController::class, 'edit'])->name('admin.settings.password');
        Route::put('/password', [AdminSettingsController::class, 'updatePassword'])->name('admin.settings.password.update');
        Route::put('/preferences', [AdminSettingsController::class, 'updatePreferences'])->name('admin.settings.preferences.update');
        Route::get('/activity-log', [AdminSettingsController::class, 'showActivityLog'])->name('admin.settings.activity-log');
        
        Route::get('/system', [AdminSystemSettingsController::class, 'index'])->name('admin.settings.system');
        Route::put('/system', [AdminSystemSettingsController::class, 'updateSettings'])->name('admin.settings.system.update');
        Route::get('/alerts', [AdminSystemSettingsController::class, 'showAlerts'])->name('admin.settings.alerts');
        Route::post('/alerts/{id}/resolve', [AdminSystemSettingsController::class, 'resolveAlert'])->name('admin.settings.alerts.resolve');
        Route::post('/alerts/clear-resolved', [AdminSystemSettingsController::class, 'clearResolvedAlerts'])->name('admin.settings.alerts.clear');
    });

    // ========== Admin Help & Documentation Routes ==========
    Route::get('/help', function() {
        return view('admin.help.index');
    })->name('admin.help');

    // ========== Promo Code Management ==========
    Route::resource('promo-codes', PromoCodesController::class, [
        'names' => [
            'index' => 'admin.promo-codes.index',
            'store' => 'admin.promo-codes.store',
            'show' => 'admin.promo-codes.show',
            'update' => 'admin.promo-codes.update',
            'destroy' => 'admin.promo-codes.destroy',
        ]
    ]);
    Route::patch('promo-codes/{promo_code}/toggle-status', [PromoCodesController::class, 'toggleStatus'])->name('admin.promo-codes.toggle-status');

    // ========== Admin Search API ==========
    Route::get('/api/search', [AdminDashboardController::class, 'search'])->name('admin.api.search');
});

// ========== Public Promo Code Routes ==========
Route::post('/api/promo-codes/apply', [PromoCodesController::class, 'applyPromoCode'])->name('api.promo-codes.apply');


// ========== API Routes with Rate Limiting ==========

// General API Routes (60 requests per minute)
Route::group(['middleware' => ['api.throttle:api', 'api.security', 'xss']], function () {
    // Centralized API for activities
    Route::get('/api/activities', [BookingController::class, 'getActivitiesApi'])->name('api.activities');
    Route::get('/api/activity-images/{id}', function($id) {
        $activity = \App\Models\Activity::find($id);
        return $activity ? $activity->swiperImages->pluck('image_path') : [];
    })->name('api.activity-images');
    Route::get('/api/booking/activities', [BookingController::class, 'getActivitiesApi'])->name('api.booking.activities');

    // API Routes for inventory (public access for preview purposes)
    Route::get('/api/inventory/{roomId}', [\App\Http\Controllers\Admin\InventoryController::class, 'getInventory'])->name('api.inventory.get');
    Route::get('/api/day-tour-inventory/{dayTourId}', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getInventory'])->name('api.day-tour-inventory.get');
});

// Booking API Routes (30 requests per minute - stricter to prevent abuse)
Route::middleware(['api.throttle:booking', 'api.security'])->group(function () {
    Route::get('/api/booking/settings', [BookingController::class, 'getBookingSettings'])->name('api.booking.settings');
    Route::post('/api/booking/calculate-staycation-price', [BookingController::class, 'calculateStaycationPrice'])->name('api.booking.calculateStaycationPrice');
    Route::get('/api/booking/staycation/availability', [BookingController::class, 'getStaycationAvailabilityApi'])->name('api.booking.staycationAvailability');
    Route::get('/api/booking/staycation/prices', [BookingController::class, 'getStaycationPricesApi'])->name('api.booking.staycationPrices');

    // Day visit API routes
    Route::get('/api/booking/dayvisit/availability', [DayVisitController::class, 'getAvailabilityApi'])->name('api.dayvisit.availability');
    Route::get('/api/booking/dayvisit/activities', [DayVisitController::class, 'getActivitiesApi'])->name('api.booking.dayvisit.activities');
});

// PayMongo Webhook Route moved to routes/api.php for stateless handling

// Upload API Routes (5 requests per minute - very strict for file uploads)
Route::group(['middleware' => ['api.throttle:upload', 'api.security', 'xss']], function () {
    // ID Verification API Routes (AI-powered real-time ID verification)
    Route::post('/api/id-verification/verify', [IdVerificationController::class, 'verify'])->name('api.id-verification.verify');
    Route::post('/api/id-verification/quick-check', [IdVerificationController::class, 'quickCheck'])->name('api.id-verification.quick-check');
    Route::post('/api/id-verification/verify-base64', [IdVerificationController::class, 'verifyBase64'])->name('api.id-verification.verify-base64');

    // API Routes for Special Events
    Route::post('/api/special-events/upload-image', [EventApiController::class, 'uploadImage']);

    // API Routes for Farm Experiences
    Route::post('/api/farm-experiences/upload-image', [\App\Http\Controllers\Api\FarmExperiencesApiController::class, 'uploadImage']);
});

// Special Events API Routes (10 requests per minute - stricter for write operations)
Route::group(['middleware' => ['api.throttle:api_strict', 'api.security', 'xss']], function () {
    Route::post('/api/special-events', [EventApiController::class, 'store']);
    Route::post('/api/special-events/{event}', [EventApiController::class, 'update']);
    Route::put('/api/special-events/{event}', [EventApiController::class, 'update']);
    Route::delete('/api/special-events/{event}', [EventApiController::class, 'destroy']);
});

// ========== Payment Callback Routes ==========
Route::get('/booking/payment-confirmation/{booking}', [PaymentController::class, 'showConfirmation'])->name('booking.payment-confirmation');
Route::get('/booking/payment-success', [PaymentController::class, 'paymentSuccess'])->name('booking.payment-success');
Route::get('/booking/payment-cancelled', [PaymentController::class, 'paymentCancelled'])->name('booking.payment-cancelled');
