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
        Schema::create('day_tour_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_tour_id')->constrained('day_tours')->onDelete('cascade');
            $table->date('date');
            $table->integer('max_groups')->default(3); // Default 3 groups per day per package
            $table->integer('booked_groups')->default(0);
            $table->integer('available_groups')->default(3); // Calculated: max - booked
            $table->boolean('is_blocked')->default(false); // Admin can block dates
            $table->text('block_reason')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate entries for same package+date
            $table->unique(['day_tour_id', 'date']);
            
            // Index for faster queries
            $table->index(['date', 'available_groups']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_tour_inventory');
    }
};
