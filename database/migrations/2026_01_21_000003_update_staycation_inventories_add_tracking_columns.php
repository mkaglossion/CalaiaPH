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
        Schema::table('staycation_inventories', function (Blueprint $table) {
            // Add new columns for tracking total and booked quantities
            $table->integer('total_quantity')->default(0)->after('date');
            $table->integer('booked_quantity')->default(0)->after('total_quantity');
            
            // Update available_quantity to have default value
            $table->integer('available_quantity')->default(0)->change();
            
            // Add index on date column for better query performance
            $table->index('date');
        });
        
        // Populate total_quantity and available_quantity from staycations table
        DB::statement('
            UPDATE staycation_inventories
            SET total_quantity = COALESCE((SELECT quantity FROM staycations WHERE id = staycation_inventories.staycation_id), 0),
                available_quantity = COALESCE(available_quantity, (SELECT quantity FROM staycations WHERE id = staycation_inventories.staycation_id), 0)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycation_inventories', function (Blueprint $table) {
            $table->dropColumn(['total_quantity', 'booked_quantity']);
            $table->dropIndex(['date']);
        });
    }
};
