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
        Schema::create('event_experience_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_id')->unique(); // e.g., 'staycation', 'activities'
            $table->string('title');
            $table->string('image_path');
            $table->text('description1');
            $table->text('description2');
            $table->string('link_path');
            $table->string('theme');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_experience_sections');
    }
};
