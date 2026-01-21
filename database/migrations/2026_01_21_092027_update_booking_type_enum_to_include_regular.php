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
        // Update booking_type enum to include 'glamping' and 'camping'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('glamping', 'camping', 'dayvisit', 'staycation') DEFAULT 'glamping'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('staycation', 'dayvisit') DEFAULT 'staycation'");
    }
};
