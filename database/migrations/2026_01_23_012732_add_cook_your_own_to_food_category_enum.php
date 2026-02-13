<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
        DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('breakfast', 'lunch', 'dinner', 'package', 'special', 'cook_your_own') DEFAULT 'package'");
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
        DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('breakfast', 'lunch', 'dinner', 'package', 'special') DEFAULT 'package'");
    }
    }
};
