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
        Schema::create('farm_info_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_info_id')->constrained('farm_infos')->onDelete('cascade');
            $table->string('section')->nullable(); // optional section name like "overview" or "details"
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_info_texts');
    }
};
