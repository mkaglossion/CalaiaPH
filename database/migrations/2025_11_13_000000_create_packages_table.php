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
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('image_public_id')->nullable();
            $table->integer('max_guests')->nullable();
            $table->string('pricing_type')->default('fixed');
            $table->boolean('is_active')->default(true);
            $table->integer('max_groups_per_day')->nullable();
            $table->json('price_configs')->nullable();
            $table->string('slug')->nullable();
            $table->integer('duration_days')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('sessions')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
