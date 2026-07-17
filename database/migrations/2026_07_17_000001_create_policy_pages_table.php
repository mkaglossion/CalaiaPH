<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nav_label', 80);
            $table->string('meta_title');
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('hero_eyebrow', 120)->nullable();
            $table->string('hero_title');
            $table->text('hero_description')->nullable();
            $table->date('last_updated');
            $table->longText('body_html');
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $configuration = require config_path('policy_pages.php');
        $now = now();

        foreach ($configuration['pages'] as $slug => $page) {
            $contentPath = resource_path($page['default_content']);

            if (! File::exists($contentPath)) {
                throw new RuntimeException('Missing default policy content: '.$contentPath);
            }

            DB::table('policy_pages')->insert([
                'slug' => $slug,
                'nav_label' => $page['nav_label'],
                'meta_title' => $page['meta_title'],
                'meta_description' => $page['meta_description'],
                'meta_keywords' => $page['meta_keywords'],
                'hero_eyebrow' => $page['hero_eyebrow'],
                'hero_title' => $page['hero_title'],
                'hero_description' => $page['hero_description'],
                'last_updated' => $page['last_updated'],
                'body_html' => File::get($contentPath),
                'is_published' => true,
                'sort_order' => $page['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_pages');
    }
};
