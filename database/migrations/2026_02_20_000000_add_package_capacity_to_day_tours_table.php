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
        // Get existing columns
        $existingColumns = DB::select("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'day_tours' AND TABLE_SCHEMA = DATABASE()
        ");
        $columnNames = array_map(fn($col) => $col->COLUMN_NAME, $existingColumns);

        Schema::table('day_tours', function (Blueprint $table) use ($columnNames) {
            // Maximum guests allowed for this package (e.g., 7 pax)
            if (!in_array('max_guests', $columnNames)) {
                $table->integer('max_guests')->default(1)->after('price');
            }
            
            // Pricing type: 'total' = package price for all guests, 'per_person' = price per person
            if (!in_array('pricing_type', $columnNames)) {
                $table->enum('pricing_type', ['total', 'per_person'])->default('per_person')->after('max_guests');
            }
            
            // Package status
            if (!in_array('is_active', $columnNames)) {
                $table->boolean('is_active')->default(true)->after('pricing_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_tours', function (Blueprint $table) {
            $table->dropColumn(['max_guests', 'pricing_type', 'is_active']);
        });
    }
};
