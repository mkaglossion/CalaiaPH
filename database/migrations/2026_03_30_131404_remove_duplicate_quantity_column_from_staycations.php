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
            if (Schema::hasColumn('staycations', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycations', function (Blueprint $table) {
            if (!Schema::hasColumn('staycations', 'quantity')) {
                $table->integer('quantity')->default(1)->after('name');
            }
        });
    }
};
