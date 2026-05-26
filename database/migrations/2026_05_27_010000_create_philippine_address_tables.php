<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            // Nullable for NCR because NCR has no province.
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->enum('type', ['city', 'municipality'])->default('city');
            $table->timestamps();

            $table->index(['region_id', 'province_id']);
            $table->unique(['region_id', 'province_id', 'name']);
        });

        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('psgc_code', 20)->unique();
            $table->string('name', 120);
            $table->timestamps();

            $table->index('city_id');
            $table->unique(['city_id', 'name']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->string('street_address', 255);
            $table->string('subdivision', 255)->nullable();
            $table->string('zip_code', 10);
            $table->string('landmark', 255)->nullable();
            $table->timestamps();

            $table->index(['region_id', 'province_id', 'city_id', 'barangay_id'], 'customer_addresses_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};

