<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom jenis pembayaran pada pembelian.
     *
     * paymentType hanya penanda jenis pembayaran (Tunai / Transfer / Tempo)
     * dan tidak terhubung ke akun kas/bank manapun.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('purchase', 'paymentType')) {
            Schema::table('purchase', function (Blueprint $table) {
                $table->string('paymentType', 20)->nullable()->after('paymentStatus')->index();
            });
        }

        // Backfill data lama: pembelian langsung yang dulu memakai akun kas/bank
        // ditandai Transfer, sisanya Tunai.
        DB::table('purchase')
            ->whereNull('paymentType')
            ->where('is_direct', true)
            ->update(['paymentType' => DB::raw("CASE WHEN userBankCode IS NULL THEN 'Tunai' ELSE 'Transfer' END")]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase', 'paymentType')) {
            Schema::table('purchase', function (Blueprint $table) {
                $table->dropColumn('paymentType');
            });
        }
    }
};
