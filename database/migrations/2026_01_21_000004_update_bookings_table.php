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
        // Check if table exists and what columns it has
        if (Schema::hasTable('bookings')) {
            // Add missing columns to existing bookings table
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'transaction_id')) {
                    $table->string('transaction_id')->unique()->after('id');
                }
                if (!Schema::hasColumn('bookings', 'customer_name')) {
                    $table->string('customer_name')->after('transaction_id');
                }
                if (!Schema::hasColumn('bookings', 'customer_email')) {
                    $table->string('customer_email')->after('customer_name');
                }
                if (!Schema::hasColumn('bookings', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_email');
                }
                if (!Schema::hasColumn('bookings', 'booking_type')) {
                    $table->enum('booking_type', ['staycation', 'dayvisit'])->default('staycation')->after('customer_phone');
                }
                if (!Schema::hasColumn('bookings', 'checkin_date')) {
                    $table->date('checkin_date')->after('booking_type');
                }
                if (!Schema::hasColumn('bookings', 'checkout_date')) {
                    $table->date('checkout_date')->nullable()->after('checkin_date');
                }
                if (!Schema::hasColumn('bookings', 'booking_data')) {
                    $table->json('booking_data')->after('checkout_date');
                }
                if (!Schema::hasColumn('bookings', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->default(0)->after('booking_data');
                }
                if (!Schema::hasColumn('bookings', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('bookings', 'payment_status')) {
                    $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->after('payment_method');
                }
                if (!Schema::hasColumn('bookings', 'status')) {
                    $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed')->after('payment_status');
                }
            });
            
            // Add indexes
            if (!$this->indexExists('bookings', 'bookings_transaction_id_unique')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('transaction_id');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_customer_email_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('customer_email');
                });
            }
            if (!$this->indexExists('bookings', 'bookings_checkin_date_checkout_date_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index(['checkin_date', 'checkout_date']);
                });
            }
            if (!$this->indexExists('bookings', 'bookings_status_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('status');
                });
            }
        } else {
            // Create new bookings table
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->unique();
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone')->nullable();
                $table->enum('booking_type', ['staycation', 'dayvisit'])->default('staycation');
                $table->date('checkin_date');
                $table->date('checkout_date')->nullable();
                $table->json('booking_data');
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
                $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
                $table->timestamps();
                
                $table->index('transaction_id');
                $table->index('customer_email');
                $table->index(['checkin_date', 'checkout_date']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $columns = ['transaction_id', 'customer_name', 'customer_email', 'customer_phone', 
                           'booking_type', 'checkin_date', 'checkout_date', 'booking_data', 
                           'total_amount', 'payment_method', 'payment_status', 'status'];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('bookings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
    
    /**
     * Check if an index exists
     */
    private function indexExists($table, $index)
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table}");
        foreach ($indexes as $idx) {
            if ($idx->Key_name === $index) {
                return true;
            }
        }
        return false;
    }
};
