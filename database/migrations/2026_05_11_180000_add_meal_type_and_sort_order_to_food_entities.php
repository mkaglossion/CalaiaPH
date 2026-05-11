<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('foods', 'meal_type')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->string('meal_type', 20)->default('all_day')->after('subcategory');
                $table->index('meal_type');
            });
        }

        if (!Schema::hasColumn('foods', 'sort_order')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('meal_type');
                $table->index('sort_order');
            });
        }

        if (!Schema::hasColumn('food_packages', 'meal_type')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->string('meal_type', 20)->default('all_day')->after('category');
                $table->index('meal_type');
            });
        }

        if (!Schema::hasColumn('food_packages', 'sort_order')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('meal_type');
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('foods', 'sort_order')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('foods', 'meal_type')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->dropIndex(['meal_type']);
                $table->dropColumn('meal_type');
            });
        }

        if (Schema::hasColumn('food_packages', 'sort_order')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('food_packages', 'meal_type')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->dropIndex(['meal_type']);
                $table->dropColumn('meal_type');
            });
        }
    }
};
