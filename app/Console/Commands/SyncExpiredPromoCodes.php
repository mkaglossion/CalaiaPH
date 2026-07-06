<?php

namespace App\Console\Commands;

use App\Models\PromoCode;
use Illuminate\Console\Command;

class SyncExpiredPromoCodes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'promo-codes:sync-expired';

    /**
     * The console command description.
     */
    protected $description = 'Automatically mark expired or exhausted promo codes as inactive.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = PromoCode::syncAutomaticStatus();

        $this->info("Expired or exhausted {$updated} promo code(s) marked inactive.");

        return self::SUCCESS;
    }
}
