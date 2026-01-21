<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use Carbon\Carbon;

class SyncStaycationInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staycation:sync-inventory {--days=90 : Number of days to sync inventory for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize staycation inventory records for upcoming dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Syncing staycation inventory for the next {$days} days...");
        
        $staycations = Staycation::all();
        
        if ($staycations->isEmpty()) {
            $this->warn('No staycations found. Please create staycation records first.');
            return 0;
        }
        
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);
        
        $totalRecords = 0;
        $updatedRecords = 0;
        $createdRecords = 0;
        
        foreach ($staycations as $staycation) {
            $this->info("Processing: {$staycation->name} (ID: {$staycation->id})");
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->toDateString();
                
                // Check if inventory record exists
                $inventory = StaycationInventory::where('staycation_id', $staycation->id)
                    ->where('date', $dateStr)
                    ->first();
                
                if ($inventory) {
                    // Update total_quantity if it changed, but preserve booked_quantity
                    if ($inventory->total_quantity != $staycation->quantity) {
                        $oldTotal = $inventory->total_quantity;
                        $inventory->total_quantity = $staycation->quantity;
                        // Recalculate available based on new total
                        $inventory->available_quantity = max(0, $staycation->quantity - $inventory->booked_quantity);
                        $inventory->save();
                        $updatedRecords++;
                        $this->line("  Updated {$dateStr}: Total {$oldTotal} → {$staycation->quantity}");
                    }
                } else {
                    // Create new inventory record
                    StaycationInventory::create([
                        'staycation_id' => $staycation->id,
                        'date' => $dateStr,
                        'total_quantity' => $staycation->quantity ?? 0,
                        'booked_quantity' => 0,
                        'available_quantity' => $staycation->quantity ?? 0,
                    ]);
                    $createdRecords++;
                }
                
                $totalRecords++;
                $currentDate->addDay();
            }
        }
        
        $this->newLine();
        $this->info("Sync completed successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Records Processed', $totalRecords],
                ['New Records Created', $createdRecords],
                ['Existing Records Updated', $updatedRecords],
            ]
        );
        
        return 0;
    }
}
