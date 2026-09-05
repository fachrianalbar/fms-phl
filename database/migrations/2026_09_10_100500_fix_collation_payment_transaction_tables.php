<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Samakan collation tabel baru (invoice_payment_transaction, invoice_payment_claim)
     * dengan tabel lama yang semuanya utf8mb4_0900_ai_ci.
     *
     * Tanpa ini, join kolom tabel baru ke tabel lama (mis. invoiceCode = invoice.code)
     * gagal dengan error 1267 "Illegal mix of collations".
     * Guard: hanya ALTER bila collation masih berbeda (aman diulang / no-op di
     * environment yang create-migration-nya sudah benar).
     */
    public function up(): void
    {
        $this->convert('utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        $this->convert('utf8mb4_unicode_ci');
    }

    private function convert(string $targetCollation): void
    {
        foreach (['invoice_payment_transaction', 'invoice_payment_claim'] as $tableName) {
            $current = collect(DB::select(
                'SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [DB::getDatabaseName(), $tableName]
            ))->first();

            if ($current === null || $current->TABLE_COLLATION === $targetCollation) {
                continue;
            }

            DB::statement("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE {$targetCollation}");
        }
    }
};
