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
            if (!Schema::hasColumn('staycation_inventories', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('available_quantity');
            }
            if (!Schema::hasColumn('staycation_inventories', 'block_reason')) {
                $table->string('block_reason')->nullable()->after('is_blocked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycation_inventories', function (Blueprint $table) {
            if (Schema::hasColumn('staycation_inventories', 'is_blocked')) {
                $table->dropColumn('is_blocked');
            }
            if (Schema::hasColumn('staycation_inventories', 'block_reason')) {
                $table->dropColumn('block_reason');
            }
        });
    }
};
