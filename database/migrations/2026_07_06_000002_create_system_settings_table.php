<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->nullable();
            $table->string('app_timezone')->nullable();
            $table->string('app_currency', 10)->nullable();
            $table->string('business_hours_start', 5)->nullable();
            $table->string('business_hours_end', 5)->nullable();
            $table->boolean('enable_new_bookings')->default(true);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->boolean('system_maintenance')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('logo_public_id')->nullable();
            $table->text('favicon_url')->nullable();
            $table->string('favicon_public_id')->nullable();
            $table->string('footer_title')->nullable();
            $table->text('footer_description')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            'app_name' => config('app.name'),
            'app_timezone' => config('app.timezone'),
            'app_currency' => 'PHP',
            'business_hours_start' => '06:00',
            'business_hours_end' => '22:00',
            'enable_new_bookings' => true,
            'vat_rate' => 0,
            'system_maintenance' => false,
            'maintenance_message' => '',
            'logo_url' => null,
            'logo_public_id' => null,
            'favicon_url' => null,
            'favicon_public_id' => null,
            'footer_title' => 'CALAIA FARM',
            'footer_description' => "Your perfect getaway destination for relaxation and adventure.\nExperience nature at its finest in Calaca, Batangas.",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
