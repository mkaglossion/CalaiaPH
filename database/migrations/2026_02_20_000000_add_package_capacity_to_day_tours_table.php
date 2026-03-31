<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: max_guests, pricing_type, is_active columns are already added 
        // by migration 2025_11_14_093721_add_missing_columns_to_day_tours_table.php
        // This migration only ensures the columns have correct defaults
        
        Schema::table('day_tours', function (Blueprint $table) {
            // Ensure max_guests has default value if column exists but is nullable
            if (Schema::hasColumn('day_tours', 'max_guests')) {
                DB::statement('UPDATE day_tours SET max_guests = 1 WHERE max_guests IS NULL');
            }
            
            // Ensure pricing_type has default value if column exists
            if (Schema::hasColumn('day_tours', 'pricing_type')) {
                DB::statement("UPDATE day_tours SET pricing_type = 'per_person' WHERE pricing_type IS NULL");
            }
            
            // Ensure is_active has default value if column exists
            if (Schema::hasColumn('day_tours', 'is_active')) {
                DB::statement('UPDATE day_tours SET is_active = 1 WHERE is_active IS NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns in down() since they're managed by earlier migration
        // This migration only sets defaults, it doesn't add columns
    }
};
