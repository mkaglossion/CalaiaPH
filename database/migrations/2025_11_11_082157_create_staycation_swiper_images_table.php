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
        Schema::create('staycation_swiper_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staycation_id')->constrained('staycations')->onDelete('cascade');
            $table->string('image_path');
            $table->string('title');
            $table->text('public_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staycation_swiper_images');
    }
};
