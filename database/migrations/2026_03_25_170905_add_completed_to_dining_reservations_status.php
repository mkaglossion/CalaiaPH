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
        // Only run on MySQL/MariaDB, skip for SQLite
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE dining_reservations MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run on MySQL/MariaDB, skip for SQLite
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE dining_reservations MODIFY status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'");
        }
    }
};
