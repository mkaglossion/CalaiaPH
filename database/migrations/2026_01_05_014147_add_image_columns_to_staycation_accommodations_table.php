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
        Schema::table('staycation_accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('staycation_accommodations', 'image_path')) {
                $table->string('image_path')->nullable()->after('price');
            }
            if (!Schema::hasColumn('staycation_accommodations', 'public_id')) {
                $table->text('public_id')->nullable()->after('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staycation_accommodations', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'public_id']);
        });
    }
};
