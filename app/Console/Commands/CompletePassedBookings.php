<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Events\BookingCompleted;
use Carbon\Carbon;

class CompletePassedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:complete-passed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark bookings as completed if their checkout date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->startOfDay();

        // Find all confirmed bookings where checkout date is in the past and they have checked in
        $bookings = Booking::where('status', 'confirmed')
            ->whereNotNull('checkout_date')
            ->where('checkout_date', '<', $today)
            ->where('checkin_status', Booking::CHECKIN_STATUS_CHECKED_IN)
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            $booking->update([
                'status' => 'completed',
                'checkin_status' => Booking::CHECKIN_STATUS_CHECKED_OUT,
                'checkout_timestamp' => now()
            ]);
            // Broadcast the event for real-time updates
            broadcast(new BookingCompleted($booking));
            $count++;
        }

        $this->info("✓ Marked {$count} bookings as completed and broadcasted.");
    }
}
