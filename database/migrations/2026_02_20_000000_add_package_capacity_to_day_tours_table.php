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
        Schema::table('day_tours', function (Blueprint $table) {
            // Maximum guests allowed for this package (e.g., 7 pax)
            $table->integer('max_guests')->default(1)->after('price');
            
            // Pricing type: 'total' = package price for all guests, 'per_person' = price per person
            $table->enum('pricing_type', ['total', 'per_person'])->default('per_person')->after('max_guests');
            
            // Package status
            $table->boolean('is_active')->default(true)->after('pricing_type');
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
