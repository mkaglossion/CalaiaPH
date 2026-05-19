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
        if (!Schema::hasColumn('staycations', 'max_guest_capacity')) {
            Schema::table('staycations', function (Blueprint $table) {
                $table->unsignedInteger('max_guest_capacity')->nullable()->after('capacity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('staycations', 'max_guest_capacity')) {
            Schema::table('staycations', function (Blueprint $table) {
                $table->dropColumn('max_guest_capacity');
            });
        }
    }
};
