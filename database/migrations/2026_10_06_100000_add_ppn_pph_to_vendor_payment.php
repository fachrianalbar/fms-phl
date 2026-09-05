<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom PPN & PPh (input manual saat generate nota vendor).
     *
     * Nilai disimpan per baris vendor_payment dalam satu nota (nilai sama
     * untuk seluruh order dalam nota). Agregasi per nota memakai MAX()
     * agar tidak terhitung ganda.
     */
    public function up(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('user_bank_code');
            $table->decimal('pph_amount', 15, 2)->default(0)->after('ppn_amount');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->dropColumn(['ppn_amount', 'pph_amount']);
        });
    }
};
