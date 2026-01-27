<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class FixMissingTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:fix-timestamps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing checkin/checkout timestamps for existing bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fix bookings that are checked_in but missing checkin_timestamp
        $checkedInBookings = Booking::where('checkin_status', 'checked_in')
            ->whereNull('checkin_timestamp')
            ->get();

        $checkinCount = 0;
        foreach ($checkedInBookings as $booking) {
            $booking->update(['checkin_timestamp' => now()]);
            $checkinCount++;
        }

        // Fix bookings that are checked_out but missing checkout_timestamp
        $checkedOutBookings = Booking::where('checkin_status', 'checked_out')
            ->whereNull('checkout_timestamp')
            ->get();

        $checkoutCount = 0;
        foreach ($checkedOutBookings as $booking) {
            $booking->update(['checkout_timestamp' => now()]);
            $checkoutCount++;
        }

        // Fix bookings that are checked_out but missing checkin_timestamp
        $checkedOutMissingCheckin = Booking::where('checkin_status', 'checked_out')
            ->whereNull('checkin_timestamp')
            ->whereNotNull('checkout_timestamp')
            ->get();

        $checkinForCheckedOutCount = 0;
        foreach ($checkedOutMissingCheckin as $booking) {
            // Set checkin timestamp to 2 hours before checkout
            $checkinTime = $booking->checkout_timestamp->copy()->subHours(2);
            $booking->update(['checkin_timestamp' => $checkinTime]);
            $checkinForCheckedOutCount++;
        }

        // Fix completed bookings that are missing timestamps
        $completedBookings = Booking::where('status', 'completed')
            ->whereNull('checkin_timestamp')
            ->get();

        $completedCount = 0;
        foreach ($completedBookings as $booking) {
            // For completed bookings, set checkin to checkout date at 2 PM, checkout to checkout date at 11 AM next day
            $checkoutDate = $booking->checkout_date ? \Carbon\Carbon::parse($booking->checkout_date) : now();
            $checkinTime = $checkoutDate->copy()->setTime(14, 0, 0); // 2 PM on checkout date
            $checkoutTime = $checkoutDate->copy()->addDay()->setTime(11, 0, 0); // 11 AM next day
            
            $booking->update([
                'checkin_timestamp' => $checkinTime,
                'checkout_timestamp' => $checkoutTime
            ]);
            $completedCount++;
        }

        // Fix completed bookings that have checkin_timestamp but missing checkout_timestamp
        $completedMissingCheckout = Booking::where('status', 'completed')
            ->whereNotNull('checkin_timestamp')
            ->whereNull('checkout_timestamp')
            ->get();

        $completedCheckoutCount = 0;
        foreach ($completedMissingCheckout as $booking) {
            // Set checkout timestamp to checkin timestamp + 1 day at 11 AM
            $checkoutTime = $booking->checkin_timestamp->copy()->addDay()->setTime(11, 0, 0);
            $booking->update(['checkout_timestamp' => $checkoutTime]);
            $completedCheckoutCount++;
        }

        $this->info("✓ Fixed {$checkinCount} missing check-in timestamps for checked-in bookings");
        $this->info("✓ Fixed {$checkoutCount} missing check-out timestamps for checked-out bookings");
        $this->info("✓ Fixed {$checkinForCheckedOutCount} missing check-in timestamps for checked-out bookings");
        $this->info("✓ Fixed {$completedCount} missing timestamps for completed bookings");
        $this->info("✓ Fixed {$completedCheckoutCount} missing checkout timestamps for completed bookings");
        $this->info("✓ Total: " . ($checkinCount + $checkoutCount + $checkinForCheckedOutCount + $completedCount + $completedCheckoutCount) . " bookings updated");
    }
}
