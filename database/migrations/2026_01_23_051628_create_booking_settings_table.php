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
        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'mattress_price_per_night', 'pet_price'
            $table->string('label'); // Human-readable label
            $table->decimal('value', 10, 2); // Numeric value for prices
            $table->string('description')->nullable();
            $table->string('category')->default('pricing'); // For grouping (pricing, policies, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_settings');
    }
};
