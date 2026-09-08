<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\PackageBooking;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpireStalePendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire-stale-pending {--minutes=1440 : Age threshold in minutes before pending PayMongo bookings are cancelled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel stale pending PayMongo bookings so abandoned checkout attempts do not remain operationally active';

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $paymentService): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $cutoff = now()->subMinutes($minutes);

        $bookings = Booking::where('payment_method', 'paymongo')
            ->where('payment_status', 'pending')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            $paymentStatus = $paymentService->getCheckoutPaymentStatus($booking->paymongo_checkout_id);
            if ($paymentStatus['status'] === 'paid') {
                $paymentService->confirmPendingBooking($booking, [
                    'checkout_id' => $booking->paymongo_checkout_id,
                    'payment_reference' => $paymentStatus['payment_reference'] ?? null,
                    'paid_amount_centavos' => $paymentStatus['paid_amount_centavos'] ?? null,
                ]);
                continue;
            }

            if ($paymentStatus['status'] === 'unknown') {
                $this->warn("Skipped booking {$booking->id}: PayMongo payment status could not be verified.");
                continue;
            }

            $paymentService->failPendingBookingForCheckout(
                $booking->paymongo_checkout_id,
                $booking->transaction_id,
                'expired',
                'stale_timeout'
            );

            $count++;
        }

        $packageBookings = PackageBooking::query()
            ->where('status', 'pending_payment')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', 'pending');
            })
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->get();

        foreach ($packageBookings as $packageBooking) {
            $packageBooking->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);

            $count++;
        }

        $this->info("Expired {$count} stale pending PayMongo booking(s) older than {$minutes} minute(s).");

        return self::SUCCESS;
    }
}
