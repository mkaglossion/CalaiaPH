<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'avatar')) {
                $table->text('avatar')->nullable()->change();
            }

            if (Schema::hasColumn('testimonials', 'image_path')) {
                $table->text('image_path')->nullable()->change();
            }
        });

        Schema::table('testimonial_section_settings', function (Blueprint $table) {
            if (Schema::hasColumn('testimonial_section_settings', 'background_image_path')) {
                $table->text('background_image_path')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'avatar')) {
                $table->string('avatar')->nullable()->change();
            }

            if (Schema::hasColumn('testimonials', 'image_path')) {
                $table->string('image_path')->nullable()->change();
            }
        });

        Schema::table('testimonial_section_settings', function (Blueprint $table) {
            if (Schema::hasColumn('testimonial_section_settings', 'background_image_path')) {
                $table->string('background_image_path')->nullable()->change();
            }
        });
    }
};
