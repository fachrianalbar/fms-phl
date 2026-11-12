<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah snapshot nama komponen biaya pada order_cost.
     *
     * Nominal (order_cost.nominal) sudah menjadi snapshot per transaksi sejak
     * order dibuat. Nama komponen perlu ikut di-snapshot agar histori tetap
     * terbaca ketika komponen master di-rename atau dihapus.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('order_cost', 'componentName')) {
            Schema::table('order_cost', function (Blueprint $table) {
                $table->string('componentName')->nullable()->after('componentType');
            });
        }

        // Isi snapshot untuk data lama dari master yang masih tersedia.
        if (Schema::hasTable('order_cost') && Schema::hasTable('cost_component') && Schema::hasColumn('order_cost', 'componentName')) {
            DB::statement(<<<'SQL'
                UPDATE `order_cost` oc
                INNER JOIN `cost_component` cc ON cc.code = oc.componentType
                SET oc.componentName = cc.name
                WHERE COALESCE(oc.componentName, '') = ''
            SQL);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_cost', 'componentName')) {
            Schema::table('order_cost', function (Blueprint $table) {
                $table->dropColumn('componentName');
            });
        }
    }
};
