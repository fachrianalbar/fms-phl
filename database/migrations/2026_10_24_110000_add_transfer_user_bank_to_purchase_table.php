<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekening bank internal yang dipakai saat jenis pembayaran Transfer.
     *
     * Kolom ini hanya menyimpan pilihan rekening internal pada form Pembelian
     * Langsung (informasi sumber transfer). Tidak membuat mutasi kas/bank;
     * mutasi tetap dibuat saat pelunasan di menu Hutang Vendor.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('purchase', 'transferUserBankCode')) {
            Schema::table('purchase', function (Blueprint $table) {
                $table->string('transferUserBankCode')->nullable()->after('paymentType')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase', 'transferUserBankCode')) {
            Schema::table('purchase', function (Blueprint $table) {
                $table->dropColumn('transferUserBankCode');
            });
        }
    }
};
