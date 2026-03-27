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
        Schema::create('admin_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_low_inventory_alerts')->default(true);
            $table->boolean('email_pending_payments')->default(true);
            $table->boolean('email_bookings')->default(true);
            $table->boolean('email_user_signups')->default(false);
            $table->string('theme_preference')->default('light');
            $table->string('language')->default('en');
            $table->json('dashboard_widgets')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        Schema::create('admin_system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'low_inventory', 'pending_payment', 'expiring_promotion'
            $table->string('severity'); // 'info', 'warning', 'critical'
            $table->text('message');
            $table->json('data')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'severity']);
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_system_alerts');
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('admin_preferences');
    }
};
