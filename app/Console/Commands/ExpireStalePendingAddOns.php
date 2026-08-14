<?php

namespace App\Console\Commands;

use App\Services\AddOnExpirationService;
use Illuminate\Console\Command;

class ExpireStalePendingAddOns extends Command
{
    protected $signature = 'add-ons:expire-stale-pending';

    protected $description = 'Expire unpaid add-on checkout attempts after their 60-minute payment window';

    public function handle(AddOnExpirationService $expirationService): int
    {
        $count = $expirationService->expireAllStale();

        $this->info("Expired {$count} stale pending add-on payment(s).");

        return self::SUCCESS;
    }
}
