<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order', 'driverName')) {
            Schema::table('order', function (Blueprint $table) {
                $table->string('driverName')->nullable()->after('driverCode');
            });
        }

        if (! Schema::hasColumn('order_driver', 'driverName')) {
            Schema::table('order_driver', function (Blueprint $table) {
                $table->string('driverName')->nullable()->after('driverCode');
            });
        }

        // Backfill snapshot dari master employee agar transaksi lama tetap memiliki nama supir.
        DB::statement('
            UPDATE `order` o
            INNER JOIN `employee` e ON e.code = o.driverCode
            SET o.driverName = e.name
            WHERE o.driverName IS NULL AND o.driverCode IS NOT NULL
        ');

        DB::statement('
            UPDATE `order_driver` od
            INNER JOIN `employee` e ON e.code = od.driverCode
            SET od.driverName = e.name
            WHERE od.driverName IS NULL AND od.driverCode IS NOT NULL
        ');
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_driver', 'driverName')) {
            Schema::table('order_driver', function (Blueprint $table) {
                $table->dropColumn('driverName');
            });
        }

        if (Schema::hasColumn('order', 'driverName')) {
            Schema::table('order', function (Blueprint $table) {
                $table->dropColumn('driverName');
            });
        }
    }
};
