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
        Schema::create('farm_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('title')->required();
            $table->text('desc')->nullable();
            $table->string('time')->nullable();
            $table->string('capacity')->nullable();
            $table->string('icon')->default('people');
            $table->string('cta')->nullable();
            $table->string('badge')->nullable();
            $table->string('img')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_experiences');
    }
};
