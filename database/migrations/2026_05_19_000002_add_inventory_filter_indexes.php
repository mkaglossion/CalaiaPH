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
        Schema::table('staycation_inventories', function (Blueprint $table) {
            $table->index(['date', 'is_blocked'], 'stay_inv_date_blocked_idx');
        });

        Schema::table('package_inventory', function (Blueprint $table) {
            $table->index(['date', 'is_blocked'], 'pkg_inv_date_blocked_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycation_inventories', function (Blueprint $table) {
            $table->dropIndex('stay_inv_date_blocked_idx');
        });

        Schema::table('package_inventory', function (Blueprint $table) {
            $table->dropIndex('pkg_inv_date_blocked_idx');
        });
    }
};
