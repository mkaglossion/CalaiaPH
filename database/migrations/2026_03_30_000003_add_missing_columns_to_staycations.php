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
        Schema::table('staycations', function (Blueprint $table) {
            if (!Schema::hasColumn('staycations', 'type')) {
                $table->enum('type', ['glamping', 'camping', 'staycation'])->default('staycation')->after('days');
            }
            if (!Schema::hasColumn('staycations', 'capacity')) {
                $table->integer('capacity')->default(2)->after('price_configs');
            }
            if (!Schema::hasColumn('staycations', 'adults')) {
                $table->integer('adults')->default(2)->after('capacity');
            }
            if (!Schema::hasColumn('staycations', 'children')) {
                $table->integer('children')->default(0)->after('adults');
            }
            if (!Schema::hasColumn('staycations', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('children');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycations', function (Blueprint $table) {
            $table->dropColumn(['type', 'capacity', 'adults', 'children', 'price']);
        });
    }
};
