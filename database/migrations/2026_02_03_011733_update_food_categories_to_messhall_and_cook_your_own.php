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
        // First, expand ENUM to include 'messhall' alongside existing values
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('breakfast', 'lunch', 'dinner', 'package', 'special', 'cook_your_own', 'messhall') DEFAULT 'package'");
        }
        
        // Then, update all existing breakfast, lunch, dinner, package, special items to messhall
        DB::table('foods')
            ->where('category', 'breakfast')
            ->orWhere('category', 'lunch')
            ->orWhere('category', 'dinner')
            ->orWhere('category', 'package')
            ->orWhere('category', 'special')
            ->update(['category' => 'messhall']);
        
        // Finally, shrink ENUM to only accept 'messhall' and 'cook_your_own'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('messhall', 'cook_your_own') DEFAULT 'messhall'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First expand ENUM to include old values
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('breakfast', 'lunch', 'dinner', 'package', 'special', 'cook_your_own', 'messhall') DEFAULT 'package'");
        }
        
        // Restore old categories from messhall (we'll default them back to package)
        DB::table('foods')
            ->where('category', 'messhall')
            ->update(['category' => 'package']);
        
        // Finally shrink ENUM back to original
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE foods MODIFY COLUMN category ENUM('breakfast', 'lunch', 'dinner', 'package', 'special', 'cook_your_own') DEFAULT 'package'");
        }
    }
};
