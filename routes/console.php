<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('add-ons:expire-stale-pending')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

// Laravel 11 loads schedules from this file. Reconcile PayMongo directly so a
// paid booking is confirmed even when the webhook delivery is missed.
Schedule::command('bookings:expire-stale-pending --minutes=' . config('booking.payment_attempt_expiry_minutes', 1440))
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
