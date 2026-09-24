<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expand financial ledger amounts beyond MySQL signed INT (2.1 billion).
     * DECIMAL(20,2) supports up to 999,999,999,999,999,999.99.
     */
    public function up(): void
    {
        $columns = [
            'live_mutation' => ['debit', 'credit', 'balance'],
            'mutation' => ['nominal'],
            'order_payment' => ['total', 'ppn', 'pph'],
            'order_payment_history' => ['total', 'ppn', 'pph'],
        ];

        foreach ($columns as $table => $tableColumns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($tableColumns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL(20,2) NULL");
                }
            }
        }
    }

    /**
     * Reverting to INT could truncate valid amounts after this migration.
     * Keep the expanded type instead of risking financial data loss.
     */
    public function down(): void
    {
        // Intentionally irreversible: existing values may exceed INT capacity.
    }
};
