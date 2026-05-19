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
        if (!Schema::hasColumn('package_inventory', 'block_reason')) {
            Schema::table('package_inventory', function (Blueprint $table) {
                $table->string('block_reason')->nullable()->after('is_blocked');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('package_inventory', 'block_reason')) {
            Schema::table('package_inventory', function (Blueprint $table) {
                $table->dropColumn('block_reason');
            });
        }
    }
};
