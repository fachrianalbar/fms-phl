<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan persentase pajak yang digunakan untuk menghitung nominal PPN/PPh
     * pada saat nota vendor dibuat.
     */
    public function up(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->decimal('ppn_rate', 8, 4)->default(0)->after('ppn_amount');
            $table->decimal('pph_rate', 8, 4)->default(0)->after('ppn_rate');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_payment', function (Blueprint $table) {
            $table->dropColumn(['ppn_rate', 'pph_rate']);
        });
    }
};
