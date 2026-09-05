<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hubungkan pembayaran per-invoice (invoice_payment) ke transaksi
     * pembayaran (invoice_payment_transaction).
     *
     * Kolom nullable agar data lama (pembayaran tanpa transaksi) tetap valid,
     * namun backfill pada migration berikutnya akan membungkus data lama
     * menjadi transaksi tunggal.
     */
    public function up(): void
    {
        Schema::table('invoice_payment', function (Blueprint $table) {
            $table->string('transactionCode', 30)->nullable()->after('invoiceCode');
            $table->index('transactionCode');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payment', function (Blueprint $table) {
            $table->dropIndex(['transactionCode']);
            $table->dropColumn('transactionCode');
        });
    }
};
