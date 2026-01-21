<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Make all non-essential columns nullable
            $columnsToMakeNullable = ['slot_id', 'guests', 'date', 'time_slot', 'package_id', 'price'];
            
            foreach ($columnsToMakeNullable as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->unsignedBigInteger($column)->nullable()->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $columnsToRevert = ['slot_id', 'guests', 'date', 'time_slot', 'package_id', 'price'];
            
            foreach ($columnsToRevert as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->unsignedBigInteger($column)->nullable(false)->change();
                }
            }
        });
    }
};
