<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('food_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable()->comment('Package price (can be discounted from individual items)');
            $table->string('category')->default('messhall')->comment('messhall, cook_your_own, special');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('stock_quantity')->default(999);
            $table->boolean('is_inventory_managed')->default(false);
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('food_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_package_id')->constrained('food_packages')->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            $table->unique(['food_package_id', 'food_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('food_package_items');
        Schema::dropIfExists('food_packages');
    }
};
