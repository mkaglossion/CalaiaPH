<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->cleanupEventSections(
            'event_sections',
            'Special Events.',
            '/special-events'
        );

        $this->cleanupEventSections(
            'event_experience_sections',
            'SPECIAL EVENTS.',
            '/special-events'
        );
    }

    public function down(): void
    {
        // One-way cleanup migration.
    }

    private function cleanupEventSections(string $table, string $title, string $linkPath): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $groupRows = DB::table($table)
            ->where(function ($query) {
                $query->where('section_id', 'groups')
                    ->orWhere('link_path', '/groups');
            });

        if (! $groupRows->exists()) {
            return;
        }

        $specialEventsExists = DB::table($table)
            ->where('section_id', 'special-events')
            ->exists();

        if ($specialEventsExists) {
            $groupRows->delete();
            return;
        }

        $groupRows->update([
            'section_id' => 'special-events',
            'title' => $title,
            'link_path' => $linkPath,
        ]);
    }
};
