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
        if (! Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description');
                $table->decimal('price', 10, 2);
                $table->enum('pricing_type', ['per_pax', 'per_group'])->default('per_pax');
                $table->string('image')->nullable();
                $table->string('image_public_id')->nullable();
                $table->integer('duration_days');
                $table->integer('max_guests');
                $table->integer('max_groups_per_day')->default(3);
                $table->json('inclusions')->nullable();
                $table->json('price_configs')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('packages', function (Blueprint $table) {
                if (! Schema::hasColumn('packages', 'image_public_id')) {
                    $table->string('image_public_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'image_public_id')) {
                $table->dropColumn('image_public_id');
            }
        });
    }
};
