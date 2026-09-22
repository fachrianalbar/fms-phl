<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('order', ['driverCode', 'orderDate'])) {
            Schema::table('order', function (Blueprint $table) {
                $table->index(['driverCode', 'orderDate'], 'order_driver_code_date_index');
            });
        }

        if (! Schema::hasIndex('order_cost', ['driverCode', 'orderCode'])) {
            Schema::table('order_cost', function (Blueprint $table) {
                $table->index(['driverCode', 'orderCode'], 'order_cost_driver_code_order_code_index');
            });
        }

        if (! Schema::hasIndex('order_driver_salary', ['status', 'driver_id', 'order_id'])) {
            Schema::table('order_driver_salary', function (Blueprint $table) {
                $table->index(['status', 'driver_id', 'order_id'], 'order_driver_salary_lookup_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_driver_salary', function (Blueprint $table) {
            $table->dropIndex('order_driver_salary_lookup_index');
        });

        Schema::table('order_cost', function (Blueprint $table) {
            $table->dropIndex('order_cost_driver_code_order_code_index');
        });

        Schema::table('order', function (Blueprint $table) {
            $table->dropIndex('order_driver_code_date_index');
        });
    }
};
