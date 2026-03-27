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
        // Skip for MariaDB - use direct ALTER instead
        if (in_array(DB::getDriverName(), ['mariadb', 'mysql'])) {
            return;
        }
        
        // Get all columns in bookings table
        if (DB::getDriverName() === 'sqlite') {
            $columns = DB::select("PRAGMA table_info(bookings)");
        } else {
            return;
            // $columns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns('bookings');
        }
        
        Schema::table('bookings', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (DB::getDriverName() === 'sqlite') {
                    $columnName = $column->name;
                    $isNullable = $column->notnull == 0;
                    $type = strtolower($column->type);
                } else {
                    $columnName = $column->getName();
                    $isNullable = !$column->getNotnull();
                    $type = $column->getType()->getName();
                }
                
                // Skip primary key, timestamps, and columns we specifically need
                $skipColumns = ['id', 'created_at', 'updated_at', 'transaction_id', 'customer_name', 
                               'customer_email', 'booking_type', 'checkin_date', 'booking_data', 
                               'total_amount', 'payment_status', 'status'];
                
                if (in_array($columnName, $skipColumns)) {
                    continue;
                }
                
                // Check if column is not nullable
                if ($isNullable) {
                    continue;
                }
                
                // Make it nullable based on type
                if (strpos($type, 'varchar') !== false || strpos($type, 'text') !== false || $type === 'string') {
                    $table->string($columnName)->nullable()->change();
                } elseif (strpos($type, 'int') !== false || strpos($type, 'bigint') !== false || $type === 'integer') {
                    $table->integer($columnName)->nullable()->change();
                } elseif (strpos($type, 'decimal') !== false || $type === 'decimal') {
                    $table->decimal($columnName, 10, 2)->nullable()->change();
                } elseif (strpos($type, 'date') !== false || $type === 'date') {
                    $table->date($columnName)->nullable()->change();
                } elseif (strpos($type, 'timestamp') !== false || $type === 'datetime') {
                    $table->timestamp($columnName)->nullable()->change();
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
