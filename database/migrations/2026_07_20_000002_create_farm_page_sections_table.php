<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->text('secondary_image_url')->nullable();
            $table->string('secondary_image_public_id')->nullable();
            $table->json('items')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $configuration = require config_path('farm_page_sections.php');
        $now = now();

        foreach ($configuration['sections'] as $key => $section) {
            DB::table('farm_page_sections')->insert([
                'key' => $key,
                'title' => $section['title'] ?? null,
                'description' => $section['description'] ?? null,
                'image_url' => $section['image_url'] ?? null,
                'image_public_id' => null,
                'secondary_image_url' => $section['secondary_image_url'] ?? null,
                'secondary_image_public_id' => null,
                'items' => json_encode($section['items'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'settings' => json_encode($section['settings'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'sort_order' => $section['sort_order'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('farm_experiences', function (Blueprint $table) {
            $table->string('image_public_id')->nullable()->after('img');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('farm_experiences', 'image_public_id')) {
            Schema::table('farm_experiences', function (Blueprint $table) {
                $table->dropColumn('image_public_id');
            });
        }

        Schema::dropIfExists('farm_page_sections');
    }
};
