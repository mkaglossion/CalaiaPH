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
        Schema::table('foods', function (Blueprint $table) {
            $table->string('category', 50)->default('package')->change();
        });
        
        // Then, update all existing breakfast, lunch, dinner, package, special items to messhall
        DB::table('foods')
            ->where('category', 'breakfast')
            ->orWhere('category', 'lunch')
            ->orWhere('category', 'dinner')
            ->orWhere('category', 'package')
            ->orWhere('category', 'special')
            ->update(['category' => 'messhall']);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->string('category', 50)->default('package')->change();
        });
        
        // Restore old categories from messhall (we'll default them back to package)
        DB::table('foods')
            ->where('category', 'messhall')
            ->update(['category' => 'package']);
    }
};
