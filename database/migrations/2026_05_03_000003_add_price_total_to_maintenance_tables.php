<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceTotalToMaintenanceTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('maintenance_detail')) {
            Schema::table('maintenance_detail', function (Blueprint $table) {
                if (! Schema::hasColumn('maintenance_detail', 'price')) {
                    $table->decimal('price', 15, 2)->default(0)->after('qty');
                }
                if (! Schema::hasColumn('maintenance_detail', 'total')) {
                    $table->decimal('total', 15, 2)->default(0)->after('price');
                }
            });
        }

        if (Schema::hasTable('maintenance')) {
            Schema::table('maintenance', function (Blueprint $table) {
                if (! Schema::hasColumn('maintenance', 'grand_total')) {
                    $table->decimal('grand_total', 15, 2)->default(0)->after('status');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('maintenance_detail')) {
            Schema::table('maintenance_detail', function (Blueprint $table) {
                if (Schema::hasColumn('maintenance_detail', 'total')) {
                    $table->dropColumn('total');
                }
                if (Schema::hasColumn('maintenance_detail', 'price')) {
                    $table->dropColumn('price');
                }
            });
        }

        if (Schema::hasTable('maintenance')) {
            Schema::table('maintenance', function (Blueprint $table) {
                if (Schema::hasColumn('maintenance', 'grand_total')) {
                    $table->dropColumn('grand_total');
                }
            });
        }
    }
}
