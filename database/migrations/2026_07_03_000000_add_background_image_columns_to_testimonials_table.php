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
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'image_public_id')) {
                $table->string('image_public_id')->nullable()->after('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'image_public_id')) {
                $table->dropColumn('image_public_id');
            }
        });
    }
};
