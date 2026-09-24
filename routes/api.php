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
