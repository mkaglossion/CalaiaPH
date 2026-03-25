<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackageBookingController;
use App\Http\Controllers\PaymentController;

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
    // Public package booking routes
    Route::post('/package-bookings', [PackageBookingController::class, 'store']);
    Route::get('/package-bookings/{id}', [PackageBookingController::class, 'show']);
    Route::post('/package-bookings/{id}/payment-status', [PackageBookingController::class, 'updatePaymentStatus']);

    // Payment routes
    Route::post('/payment/create-checkout', [PaymentController::class, 'createCheckout']);
    Route::get('/payment/confirm', [PaymentController::class, 'confirmCheckout']);
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']);
});
