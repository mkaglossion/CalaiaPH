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
        Schema::create('activity_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('special'); // 'adventure', 'wellness', 'cultural', 'special'
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('stock_quantity')->nullable();
            $table->boolean('is_inventory_managed')->default(false);
            $table->timestamps();
            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('activity_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_package_id')->constrained('activity_packages')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['activity_package_id', 'activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_package_items');
        Schema::dropIfExists('activity_packages');
    }
};
