<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonial_section_settings', function (Blueprint $table) {
            $table->id();
            $table->string('background_image_path')->nullable();
            $table->string('background_image_public_id')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('testimonials')) {
            $query = DB::table('testimonials');

            $columns = Schema::hasColumn('testimonials', 'background_image_path')
                ? ['background_image_path']
                : [];

            if (Schema::hasColumn('testimonials', 'background_image_public_id')) {
                $columns[] = 'background_image_public_id';
            }

            if (!empty($columns)) {
                $row = $query
                    ->whereNotNull('background_image_path')
                    ->where('background_image_path', '!=', '')
                    ->orderBy('id')
                    ->first($columns);

                if ($row) {
                    DB::table('testimonial_section_settings')->insert([
                        'background_image_path' => $row->background_image_path ?? null,
                        'background_image_public_id' => $row->background_image_public_id ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_section_settings');
    }
};
