<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom Biaya Claim (potongan nominal saat generate nota vendor).
     *
     * Nilai disimpan per baris vendor_payment dalam satu nota (nilai sama
     * untuk seluruh order dalam nota, mengikuti pola ppn_amount/pph_amount).
     * Agregasi per nota memakai MAX() agar tidak terhitung ganda.
     * Kolom amount tiap order sudah neto setelah dikurangi porsi claim-nya.
     */
    public function up(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->decimal('claim_amount', 15, 2)->default(0)->after('pph_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->dropColumn('claim_amount');
        });
    }
};
