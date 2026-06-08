<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class PurgeOldPaymentAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:purge-old-attempts {--days=30 : Retention period in days for failed or expired PayMongo payment attempts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old failed or expired PayMongo payment attempts after the retention window';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = Booking::where('payment_method', 'paymongo')
            ->where('payment_status', 'failed')
            ->where('status', 'cancelled')
            ->where('created_at', '<=', $cutoff)
            ->delete();

        $this->info("Purged {$deleted} old PayMongo payment attempt(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
