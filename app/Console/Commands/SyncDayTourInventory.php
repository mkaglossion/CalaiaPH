<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DayTour;
use App\Models\DayTourInventory;
use Carbon\Carbon;

class SyncDayTourInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daytour:sync-inventory {--days=90 : Number of days to sync inventory for} {--daytour= : Specific day tour ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize day tour inventory records for upcoming dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dayTourId = $this->option('daytour');

        $this->info("Syncing day tour inventory for the next {$days} days...");

        $dayTours = $dayTourId ? DayTour::where('id', $dayTourId)->get() : DayTour::all();

        if ($dayTours->isEmpty()) {
            $this->warn('No day tours found. Please create day tour records first.');
            return 0;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);

        $totalRecords = 0;
        $updatedRecords = 0;
        $createdRecords = 0;

        foreach ($dayTours as $dayTour) {
            $this->info("Processing: {$dayTour->name} (ID: {$dayTour->id})");

            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->toDateString();

                // Check if inventory record exists
                $inventory = DayTourInventory::where('day_tour_id', $dayTour->id)
                    ->where('date', $dateStr)
                    ->first();

                if ($inventory) {
                    // Update max_groups if it changed, but preserve booked_groups
                    $maxGroups = $dayTour->max_groups_per_day ?? 3;
                    if ($inventory->max_groups != $maxGroups) {
                        $oldMax = $inventory->max_groups;
                        $inventory->max_groups = $maxGroups;
                        // Recalculate available based on new max_groups
                        $inventory->available_groups = max(0, $maxGroups - $inventory->booked_groups);
                        $inventory->save();
                        $updatedRecords++;
                        $this->line("  Updated {$dateStr}: Max Groups {$oldMax} → {$maxGroups}");
                    }
                } else {
                    // Create new inventory record
                    $maxGroups = $dayTour->max_groups_per_day ?? 3;
                    DayTourInventory::create([
                        'day_tour_id' => $dayTour->id,
                        'date' => $dateStr,
                        'max_groups' => $maxGroups,
                        'booked_groups' => 0,
                        'available_groups' => $maxGroups,
                        'is_blocked' => false,
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
