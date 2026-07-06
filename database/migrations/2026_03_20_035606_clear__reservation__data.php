<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Intentionally left blank.
        // This migration used to truncate live reservation tables, which caused
        // data loss on future installs or when replaying migrations.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data clearing migration - reversal is not applicable
    }
};
