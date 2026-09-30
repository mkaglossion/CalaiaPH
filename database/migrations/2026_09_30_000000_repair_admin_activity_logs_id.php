<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair the existing activity-log primary key without recreating the table.
     */
    public function up(): void
    {
        if (! Schema::hasTable('admin_activity_logs') || ! Schema::hasColumn('admin_activity_logs', 'id')) {
            return;
        }

        $connection = Schema::getConnection();

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $column = $connection->selectOne(
            'SELECT IS_NULLABLE, COLUMN_KEY, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['admin_activity_logs', 'id']
        );

        if (! $column) {
            return;
        }

        $nullIds = $connection->selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM `admin_activity_logs`
             WHERE `id` IS NULL'
        );

        if ((int) $nullIds->aggregate > 0) {
            throw new \RuntimeException(
                'Cannot repair admin_activity_logs.id safely because existing rows contain NULL IDs.'
            );
        }

        $duplicateId = $connection->selectOne(
            'SELECT `id`
             FROM `admin_activity_logs`
             GROUP BY `id`
             HAVING COUNT(*) > 1
             LIMIT 1'
        );

        if ($duplicateId) {
            throw new \RuntimeException(
                'Cannot repair admin_activity_logs.id safely because existing IDs are duplicated.'
            );
        }

        $primaryColumns = collect($connection->select(
            'SHOW INDEX FROM `admin_activity_logs` WHERE Key_name = ?',
            ['PRIMARY']
        ))
            ->sortBy(fn (object $index): int => (int) $index->Seq_in_index)
            ->pluck('Column_name')
            ->values()
            ->all();

        if ($primaryColumns !== [] && $primaryColumns !== ['id']) {
            throw new \RuntimeException(
                'Cannot repair admin_activity_logs.id safely because another primary-key definition exists.'
            );
        }

        $needsAutoIncrement = ! str_contains(strtolower((string) $column->EXTRA), 'auto_increment');
        $needsPrimaryKey = $primaryColumns !== ['id'];

        if (! $needsAutoIncrement && ! $needsPrimaryKey) {
            return;
        }

        $alterations = [];

        if ($needsAutoIncrement) {
            $alterations[] = 'MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
        }

        if ($needsPrimaryKey) {
            $alterations[] = 'ADD PRIMARY KEY (`id`)';
        }

        $connection->statement(
            'ALTER TABLE `admin_activity_logs` '.implode(', ', $alterations)
        );
    }

    /**
     * Do not remove the repaired invariant during rollback.
     * Reverting it would reintroduce the production insert failure.
     */
    public function down(): void
    {
        // Intentionally left blank for safe rollback behavior.
    }
};
