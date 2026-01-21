<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all columns in bookings table
        $columns = DB::select("DESCRIBE bookings");
        
        Schema::table('bookings', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                $columnName = $column->Field;
                
                // Skip primary key, timestamps, and columns we specifically need
                $skipColumns = ['id', 'created_at', 'updated_at', 'transaction_id', 'customer_name', 
                               'customer_email', 'booking_type', 'checkin_date', 'booking_data', 
                               'total_amount', 'payment_status', 'status'];
                
                if (in_array($columnName, $skipColumns)) {
                    continue;
                }
                
                // Check if column is not nullable
                if ($column->Null === 'NO') {
                    // Make it nullable based on type
                    if (strpos($column->Type, 'varchar') !== false || strpos($column->Type, 'text') !== false) {
                        $table->string($columnName)->nullable()->change();
                    } elseif (strpos($column->Type, 'int') !== false || strpos($column->Type, 'bigint') !== false) {
                        $table->integer($columnName)->nullable()->change();
                    } elseif (strpos($column->Type, 'decimal') !== false) {
                        $table->decimal($columnName, 10, 2)->nullable()->change();
                    } elseif (strpos($column->Type, 'date') !== false) {
                        $table->date($columnName)->nullable()->change();
                    } elseif (strpos($column->Type, 'timestamp') !== false) {
                        $table->timestamp($columnName)->nullable()->change();
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversing as it could cause data loss
    }
};
