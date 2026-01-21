<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Staycation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, ensure the enum is updated to include glamping and camping
        DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_type ENUM('glamping', 'camping', 'dayvisit', 'staycation', 'regular') DEFAULT 'glamping'");
        
        // Get all staycations to map IDs to types
        $staycations = Staycation::all()->keyBy('id');
        
        // Glamping accommodation names
        $glampingNames = ['Standard Glamping', 'Premium Glamping', 'Standard Family Glamping Tent', 'Premium Family Glamping Tent'];
        
        // Update existing staycation bookings to glamping or camping
        $bookings = DB::table('bookings')->where('booking_type', 'staycation')->get();
        
        foreach ($bookings as $booking) {
            $bookingData = json_decode($booking->booking_data, true);
            $newType = 'camping'; // default to camping
            
            // Check first accommodation to determine type
            if (isset($bookingData['accommodations'][0])) {
                $accommodation = $bookingData['accommodations'][0];
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                
                // Check by staycation ID
                if ($staycationId && isset($staycations[$staycationId])) {
                    $staycationName = $staycations[$staycationId]->name;
                    if (in_array($staycationName, $glampingNames)) {
                        $newType = 'glamping';
                    }
                }
                // Fallback: check by accommodation name in booking data
                elseif (isset($accommodation['name'])) {
                    if (in_array($accommodation['name'], $glampingNames)) {
                        $newType = 'glamping';
                    }
                }
            }
            
            DB::table('bookings')
                ->where('id', $booking->id)
                ->update(['booking_type' => $newType]);
        }
        
        // Update 'regular' bookings to 'dayvisit'
        DB::table('bookings')
            ->where('booking_type', 'regular')
            ->update(['booking_type' => 'dayvisit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert glamping and camping back to staycation
        DB::table('bookings')
            ->whereIn('booking_type', ['glamping', 'camping'])
            ->update(['booking_type' => 'staycation']);
    }
};
