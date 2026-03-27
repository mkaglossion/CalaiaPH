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
        Schema::table('activities', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('activities', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('duration');
            }
            
            if (!Schema::hasColumn('activities', 'availability')) {
                $table->string('availability')->nullable()->after('price');
            }
            
            if (!Schema::hasColumn('activities', 'image')) {
                $table->string('image')->nullable()->after('availability');
            }
            
            if (!Schema::hasColumn('activities', 'category')) {
                $table->string('category')->nullable()->after('image');
            }
            
            if (!Schema::hasColumn('activities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('category');
            }
            
            if (!Schema::hasColumn('activities', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $columns = ['price', 'availability', 'image', 'category', 'is_active', 'sort_order'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
