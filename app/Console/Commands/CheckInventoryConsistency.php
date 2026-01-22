<?php

namespace App\Console\Commands;

use App\Models\StaycationInventory;
use Illuminate\Console\Command;

class CheckInventoryConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-consistency {--fix : Automatically fix inconsistencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and optionally fix inventory data consistency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking inventory consistency...');

        $inventories = StaycationInventory::all();
        $inconsistent = 0;
        $fixed = 0;

        foreach ($inventories as $inventory) {
            $expectedAvailable = max(0, $inventory->total_quantity - $inventory->booked_quantity);

            if ($inventory->available_quantity != $expectedAvailable) {
                $inconsistent++;
                $this->warn("Inconsistent: Staycation {$inventory->staycation_id} on {$inventory->date} - Expected available: {$expectedAvailable}, Actual: {$inventory->available_quantity}");

                if ($this->option('fix')) {
                    $inventory->available_quantity = $expectedAvailable;
                    $inventory->save();
                    $fixed++;
                    $this->info("Fixed: Updated available_quantity to {$expectedAvailable}");
                }
            }
        }

        $this->info("Check completed. Found {$inconsistent} inconsistent records.");
        if ($this->option('fix')) {
            $this->info("Fixed {$fixed} records.");
        } else {
            $this->info("Run with --fix to automatically correct inconsistencies.");
        }

        return 0;
    }
}
