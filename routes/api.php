<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackageBookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Api\PaymongoWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware('api')->group(function () {
    // Address cascading endpoints (PSGC-based hierarchy)
    Route::get('/address/provinces/{region}', [AddressController::class, 'provinces']);
    Route::get('/address/cities/{province}', [AddressController::class, 'cities']);
    Route::get('/address/cities-by-region/{region}', [AddressController::class, 'citiesByRegion']);
    Route::get('/address/barangays/{city}', [AddressController::class, 'barangays']);
    Route::post('/customer-addresses', [AddressController::class, 'store'])
        ->middleware(['api.throttle:api', 'api.security', 'xss']);

    // Public package booking routes
    Route::post('/package-bookings', [PackageBookingController::class, 'store'])
        ->middleware(['api.throttle:booking', 'api.security', 'xss']);

    // Payment routes
    Route::post('/payment/create-checkout', [PaymentController::class, 'createCheckout'])
        ->middleware(['api.throttle:api', 'api.security']);
    Route::get('/payment/confirm', [PaymentController::class, 'confirmCheckout'])
        ->middleware('api.throttle:api');

    // Webhooks authenticate with PayMongo's signature, then receive a modest abuse limit.
    Route::post('/payment/webhook', [PaymongoWebhookController::class, 'handle'])
        ->middleware('api.throttle:api');
    // PayMongo webhook (stateless API route)
    Route::post('/webhooks/paymongo', [PaymongoWebhookController::class, 'handle'])
        ->middleware('api.throttle:api');
    // Backwards-compatible route for production domain registration
    Route::post('/paymongo/webhook', [PaymongoWebhookController::class, 'handle'])
        ->middleware('api.throttle:api');
});

// General booking/pricing API routes (60 req/min)
Route::middleware(['api.throttle:api', 'api.security', 'xss'])->group(function () {
    Route::get('/activities', [\App\Http\Controllers\BookingController::class, 'getActivitiesApi'])->name('api.activities');
    Route::get('/activity-images/{id}', function($id) {
        $activity = \App\Models\Activity::find($id);
        return $activity ? $activity->swiperImages->pluck('image_path') : [];
    })->name('api.activity-images');
    Route::get('/booking/activities', [\App\Http\Controllers\BookingController::class, 'getActivitiesApi'])->name('api.booking.activities');

    // API Routes for inventory (public access for preview purposes)
    Route::get('/inventory/{roomId}', [\App\Http\Controllers\Admin\InventoryController::class, 'getInventory'])->name('api.inventory.get');
    Route::get('/day-tour-inventory/{dayTourId}', [\App\Http\Controllers\Admin\DayTourInventoryController::class, 'getInventory'])->name('api.day-tour-inventory.get');

    // Pricing API routes
    Route::get('/pricing/peak-types', [\App\Http\Controllers\SeasonDurationController::class, 'getPeakTypes'])->name('api.pricing.peakTypes');
    Route::get('/pricing/holidays', [\App\Http\Controllers\SeasonDurationController::class, 'getHolidays'])->name('api.pricing.holidays');
    Route::get('/pricing/weekend-config', [\App\Http\Controllers\SeasonDurationController::class, 'getWeekendConfig'])->name('api.pricing.weekendConfig');

    // Real-time pricing endpoints for calendar
    Route::post('/pricing/day-tour', [\App\Http\Controllers\BookingController::class, 'calculateDayTourPrice'])->name('api.pricing.dayTour');
    Route::post('/pricing/staycation', [\App\Http\Controllers\BookingController::class, 'calculateStaycationPriceCalendar'])->name('api.pricing.staycationCalendar');

    // VAT rate configuration (public access for pricing calculations)
    Route::get('/vat-rate', function() {
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
    Route::get('/booking/settings', [\App\Http\Controllers\BookingController::class, 'getBookingSettings'])->name('api.booking.settings');
    Route::post('/booking/calculate-staycation-price', [\App\Http\Controllers\BookingController::class, 'calculateStaycationPrice'])->name('api.booking.calculateStaycationPrice');
    Route::get('/booking/staycation/availability', [\App\Http\Controllers\BookingController::class, 'getStaycationAvailabilityApi'])->name('api.booking.staycationAvailability');
    Route::get('/booking/staycation/prices', [\App\Http\Controllers\BookingController::class, 'getStaycationPricesApi'])->name('api.booking.staycationPrices');

    // Day visit API routes
    Route::get('/booking/dayvisit/availability', [\App\Http\Controllers\DayVisitController::class, 'getAvailabilityApi'])->name('api.dayvisit.availability');
    Route::get('/booking/dayvisit/activities', [\App\Http\Controllers\DayVisitController::class, 'getActivitiesApi'])->name('api.booking.dayvisit.activities');
});
