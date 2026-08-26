<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staycations') || ! Schema::hasTable('staycation_texts')) {
            return;
        }

        $staycationIds = DB::table('staycations')
            ->whereIn('name', ['Standard Glamping', 'Standard Glamping Tent'])
            ->pluck('id');

        DB::table('staycation_texts')
            ->whereIn('staycation_id', $staycationIds)
            ->where('section', 'features')
            ->orderBy('id')
            ->each(function (object $text): void {
                $content = (string) $text->content;
                $corrected = str_replace(
                    [
                        'Exclusive toilet and heated shower a few steps away from tent',
                        'Bathroom amenities: bath towels, body soap, shampoo, dental, vanity kits',
                    ],
                    [
                        'Exclusive toilet and heated shower located a few steps away from the tent.',
                        'Bathroom amenities: bath towels, body soap, shampoo, dental, and vanity kits',
                    ],
                    $content
                );

                if ($corrected !== $content) {
                    DB::table('staycation_texts')->where('id', $text->id)->update([
                        'content' => $corrected,
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep approved copy changes and avoid overwriting later admin edits.
    }
};
