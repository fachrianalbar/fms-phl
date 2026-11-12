<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Isi snapshot driverName & plateNumber untuk data transaksi lama.
     *
     * Idempotent: hanya mengisi baris yang snapshot-nya masih NULL/kosong,
     * sehingga aman dijalankan berulang di environment mana pun.
     */
    public function up(): void
    {
        // Snapshot nama supir pada order.
        if (Schema::hasTable('order') && Schema::hasTable('employee') && Schema::hasColumn('order', 'driverName')) {
            DB::statement(<<<'SQL'
                UPDATE `order` o
                INNER JOIN `employee` e ON e.code = o.driverCode
                SET o.driverName = e.name
                WHERE COALESCE(o.driverName, '') = ''
                  AND COALESCE(o.driverCode, '') <> ''
            SQL);
        }

        // Snapshot nama supir pada histori perubahan supir.
        if (Schema::hasTable('order_driver') && Schema::hasTable('employee') && Schema::hasColumn('order_driver', 'driverName')) {
            DB::statement(<<<'SQL'
                UPDATE `order_driver` od
                INNER JOIN `employee` e ON e.code = od.driverCode
                SET od.driverName = e.name
                WHERE COALESCE(od.driverName, '') = ''
                  AND COALESCE(od.driverCode, '') <> ''
            SQL);
        }

        // Snapshot plat nomor pada order.
        if (Schema::hasTable('order') && Schema::hasTable('fleet') && Schema::hasColumn('order', 'plateNumber')) {
            DB::statement(<<<'SQL'
                UPDATE `order` o
                INNER JOIN `fleet` f ON f.code = o.fleetCode
                SET o.plateNumber = f.plateNumber
                WHERE COALESCE(o.plateNumber, '') = ''
                  AND COALESCE(o.fleetCode, '') <> ''
            SQL);
        }
    }

    /**
     * Backfill data tidak dibalik (hanya mengisi snapshot yang kosong).
     */
    public function down(): void
    {
        //
    }
};
