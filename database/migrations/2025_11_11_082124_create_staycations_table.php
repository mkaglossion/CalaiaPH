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
        Schema::create('staycations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days')->default(1);
            $table->enum('type', ['glamping', 'camping', 'staycation'])->default('staycation');
            $table->integer('base_quantity')->default(1);
            $table->json('price_configs')->nullable();
            $table->integer('capacity')->default(2);
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staycations');
    }
};
