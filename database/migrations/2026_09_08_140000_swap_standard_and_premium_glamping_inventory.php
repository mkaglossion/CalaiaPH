<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $capacities = [
                'Standard Glamping' => 4,
                'Premium Glamping' => 2,
            ];

            foreach ($capacities as $name => $targetQuantity) {
                $staycation = DB::table('staycations')
                    ->where('name', $name)
                    ->first(['id']);

                if (!$staycation) {
                    continue;
                }

                if (Schema::hasColumn('staycations', 'base_quantity')) {
                    DB::table('staycations')
                        ->where('id', $staycation->id)
                        ->update(['base_quantity' => $targetQuantity]);
                }

                $inventories = DB::table('staycation_inventories')
                    ->where('staycation_id', $staycation->id)
                    ->lockForUpdate()
                    ->get(['id', 'booked_quantity']);

                foreach ($inventories as $inventory) {
                    $bookedQuantity = (int) $inventory->booked_quantity;
                    $totalQuantity = max($targetQuantity, $bookedQuantity);

                    DB::table('staycation_inventories')
                        ->where('id', $inventory->id)
                        ->update([
                            'total_quantity' => $totalQuantity,
                            'available_quantity' => max(0, $totalQuantity - $bookedQuantity),
                        ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $capacities = [
                'Standard Glamping' => 2,
                'Premium Glamping' => 4,
            ];

            foreach ($capacities as $name => $targetQuantity) {
                $staycation = DB::table('staycations')
                    ->where('name', $name)
                    ->first(['id']);

                if (!$staycation) {
                    continue;
                }

                if (Schema::hasColumn('staycations', 'base_quantity')) {
                    DB::table('staycations')
                        ->where('id', $staycation->id)
                        ->update(['base_quantity' => $targetQuantity]);
                }

                $inventories = DB::table('staycation_inventories')
                    ->where('staycation_id', $staycation->id)
                    ->lockForUpdate()
                    ->get(['id', 'booked_quantity']);

                foreach ($inventories as $inventory) {
                    $bookedQuantity = (int) $inventory->booked_quantity;
                    $totalQuantity = max($targetQuantity, $bookedQuantity);

                    DB::table('staycation_inventories')
                        ->where('id', $inventory->id)
                        ->update([
                            'total_quantity' => $totalQuantity,
                            'available_quantity' => max(0, $totalQuantity - $bookedQuantity),
                        ]);
                }
            }
        });
    }
};
