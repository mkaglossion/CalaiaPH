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
        Schema::create('dining_category_images', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['messhall', 'cook_your_own'])->unique();
            $table->string('image_path');
            $table->text('public_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_category_images');
    }
};
