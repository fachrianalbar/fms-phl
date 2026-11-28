<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perbaikan data hasil keluhan user perawatan/gudang (2026-11-28):
     *
     * 1. Isi ulang purchase_detail.qtyUsed dari total pemakaian maintenance_fifo
     *    untuk data lama yang qtyUsed-nya masih NULL/kurang, supaya PO yang
     *    isinya sudah habis terpakai tidak muncul lagi di dropdown perawatan.
     * 2. Betulkan tipe item yang tertukar: JASA-41 (SERVICE DINAMO) seharusnya
     *    'jasa', ITM-686 (BAUT CUCUK) seharusnya 'part'.
     *
     * Idempotent: hanya mengubah baris yang masih salah/kurang, sehingga aman
     * dijalankan di environment mana pun (sandbox maupun prod).
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_detail') && Schema::hasTable('maintenance_fifo') && Schema::hasColumn('purchase_detail', 'qtyUsed')) {
            DB::statement(<<<'SQL'
                UPDATE `purchase_detail` pd
                INNER JOIN (
                    SELECT purchaseDetailCode, SUM(qty) AS totalUsed
                    FROM `maintenance_fifo`
                    GROUP BY purchaseDetailCode
                ) f ON f.purchaseDetailCode = pd.code
                SET pd.qtyUsed = f.totalUsed
                WHERE pd.deleted_at IS NULL
                  AND f.totalUsed > COALESCE(pd.qtyUsed, 0)
            SQL);
        }

        if (Schema::hasTable('item')) {
            DB::statement("UPDATE `item` SET `type` = 'jasa' WHERE `code` = 'JASA-41' AND `type` = 'part'");
            DB::statement("UPDATE `item` SET `type` = 'part' WHERE `code` = 'ITM-686' AND `type` = 'jasa'");
        }
    }

    /**
     * Tipe item dikembalikan; backfill qtyUsed tidak dibalik
     * (nilai lama NULL tidak dapat dipulihkan secara aman).
     */
    public function down(): void
    {
        if (Schema::hasTable('item')) {
            DB::statement("UPDATE `item` SET `type` = 'part' WHERE `code` = 'JASA-41' AND `type` = 'jasa'");
            DB::statement("UPDATE `item` SET `type` = 'jasa' WHERE `code` = 'ITM-686' AND `type` = 'part'");
        }
    }
};
