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
        Schema::create('dining_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('food_id');
            $table->foreign('food_id')->references('id')->on('foods')->onDelete('cascade');
            $table->enum('meal_period', ['breakfast', 'lunch', 'snack', 'dinner', 'supper']);
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('salutation')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->text('special_requests')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_reservations');
    }
};
