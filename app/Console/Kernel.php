<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the command to mark passed bookings as completed every day at midnight
        $schedule->command('bookings:complete-passed')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->onOneServer();

        // Expire abandoned PayMongo checkout attempts so only operational bookings remain active.
        $schedule->command('bookings:expire-stale-pending --minutes=' . config('booking.payment_attempt_expiry_minutes', 1440))
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('bookings:purge-old-attempts --days=30')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('promo-codes:sync-expired')
            ->dailyAt('00:10')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
