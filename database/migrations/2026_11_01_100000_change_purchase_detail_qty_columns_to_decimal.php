<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom receivedQty & qtyUsed sebelumnya bertipe int, sedangkan qty sudah
     * decimal(10,1). Akibatnya pembelian spare part dengan qty 0.5 dibulatkan
     * menjadi 1 sehingga sisa qty PO, laporan pembelian, dan alokasi FIFO
     * pemakaian spare part tidak sesuai.
     */
    public function up(): void
    {
        Schema::table('purchase_detail', function (Blueprint $table) {
            $table->decimal('receivedQty', 10, 1)->nullable()->change();
            $table->decimal('qtyUsed', 10, 1)->nullable()->change();
        });

        $this->fixRoundedReceivedQty();
        $this->fixRoundedQtyUsed();
    }

    public function down(): void
    {
        Schema::table('purchase_detail', function (Blueprint $table) {
            $table->integer('receivedQty')->nullable()->change();
            $table->integer('qtyUsed')->nullable()->change();
        });
    }

    /**
     * Koreksi receivedQty yang membesar karena pembulatan qty pecahan
     * (contoh: qty 0.5 tersimpan sebagai receivedQty 1).
     */
    private function fixRoundedReceivedQty(): void
    {
        DB::table('purchase_detail')
            ->whereNull('deleted_at')
            ->whereNotNull('receivedQty')
            ->whereRaw('qty <> FLOOR(qty)')
            ->whereRaw('receivedQty > qty')
            ->whereRaw('receivedQty = CEIL(qty)')
            ->update(['receivedQty' => DB::raw('qty')]);
    }

    /**
     * Koreksi qtyUsed yang membesar karena pembulatan setengah. Nilai acuan
     * pemakaian adalah total qty pada maintenance_fifo.
     */
    private function fixRoundedQtyUsed(): void
    {
        DB::statement(<<<'SQL'
            UPDATE purchase_detail pd
            JOIN (
                SELECT purchaseDetailCode, SUM(qty) AS fifoQty
                FROM maintenance_fifo
                GROUP BY purchaseDetailCode
            ) fifo ON fifo.purchaseDetailCode = pd.code
            SET pd.qtyUsed = fifo.fifoQty
            WHERE pd.deleted_at IS NULL
              AND pd.qtyUsed IS NOT NULL
              AND pd.qtyUsed <> fifo.fifoQty
              AND pd.qtyUsed = ROUND(fifo.fifoQty + 0.5, 0)
        SQL);
    }
};
