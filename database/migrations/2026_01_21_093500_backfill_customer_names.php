<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->select('id', 'customer_name', 'customer_email', 'booking_data')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $currentName = trim((string) ($row->customer_name ?? ''));
                    if ($currentName !== '' && $currentName !== 'N/A') {
                        continue;
                    }

                    $data = json_decode($row->booking_data ?? '', true) ?? [];

                    // Try to get name from booking_data
                    $first = $data['customer_info']['first_name']
                        ?? $data['customerInfo']['first_name']
                        ?? $data['first_name']
                        ?? '';

                    $last = $data['customer_info']['last_name']
                        ?? $data['customerInfo']['last_name']
                        ?? $data['last_name']
                        ?? '';

                    $name = trim($first . ' ' . $last);
                    if ($name === '') {
                        $name = $data['customer_info']['name']
                            ?? $data['customerInfo']['name']
                            ?? $data['name']
                            ?? '';
                    }

                    // Only update if we found a real name (not email-derived)
                    if ($name !== '' && $name !== 'N/A') {
                        DB::table('bookings')
                            ->where('id', $row->id)
                            ->update(['customer_name' => $name]);
                    }
                }
            });
    }

    public function down(): void
    {
        // no-op rollback
    }
};
