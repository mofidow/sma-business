<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (env('DB_CONNECTION') === 'sqlite') {
            // SQLite does not support dropping columns directly; skipping migration.
            return;
        }
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $foreignKeyRows = $connection->select(
            <<<'SQL'
                SELECT TABLE_NAME AS table_name, CONSTRAINT_NAME AS constraint_name
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE CONSTRAINT_SCHEMA = ?
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            SQL,
            [$database, 'company_id']
        );

        $foreignKeysByTable = [];

        foreach ($foreignKeyRows as $row) {
            $foreignKeysByTable[$row->table_name][] = $row->constraint_name;
        }

        foreach ($foreignKeysByTable as $tableName => $constraintNames) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $constraintNames = array_values(array_unique($constraintNames));

            Schema::table($tableName, function (Blueprint $table) use ($constraintNames) {
                foreach ($constraintNames as $constraintName) {
                    $table->dropForeign($constraintName);
                }
            });
        }

        $indexRows = $connection->select(
            <<<'SQL'
                SELECT DISTINCT TABLE_NAME AS table_name, INDEX_NAME AS index_name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                  AND COLUMN_NAME = ?
                  AND INDEX_NAME <> 'PRIMARY'
            SQL,
            [$database, 'company_id']
        );

        $indexesByTable = [];

        foreach ($indexRows as $row) {
            $indexesByTable[$row->table_name][] = $row->index_name;
        }

        foreach ($indexesByTable as $tableName => $indexNames) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $indexNames = array_values(array_unique($indexNames));

            Schema::table($tableName, function (Blueprint $table) use ($indexNames) {
                foreach ($indexNames as $indexName) {
                    $table->dropIndex($indexName);
                }
            });
        }

        $tablesWithCompanyId = $connection->select(
            <<<'SQL'
                SELECT DISTINCT TABLE_NAME AS table_name
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND COLUMN_NAME = ?
            SQL,
            [$database, 'company_id']
        );

        foreach ($tablesWithCompanyId as $row) {
            $tableName = $row->table_name;

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('companies');
    }

    public function down(): void {}
};
