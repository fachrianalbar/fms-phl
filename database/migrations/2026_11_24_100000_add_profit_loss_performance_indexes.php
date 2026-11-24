<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfMissing('fleet', ['code'], 'fleet_code_index');
        $this->addIndexIfMissing('fleet', ['fleetCompanyCode', 'deleted_at'], 'fleet_company_deleted_index');

        $this->addIndexIfMissing('order', ['fleetCode', 'orderDate', 'deleted_at'], 'order_fleet_date_deleted_index');
        $this->addIndexIfMissing('order', ['routeCode'], 'order_route_index');

        $this->addIndexIfMissing('route_detail', ['routeCode'], 'route_detail_route_index');
        $this->addIndexIfMissing('route_detail', ['componentCode'], 'route_detail_component_index');

        $this->addIndexIfMissing('invoice_detail', ['orderCode'], 'invoice_detail_order_index');
        $this->addIndexIfMissing('invoice_detail', ['invoiceCode'], 'invoice_detail_invoice_index');

        $this->addIndexIfMissing('order_payment', ['orderCode'], 'order_payment_order_index');

        $this->addIndexIfMissing('maintenance', ['code'], 'maintenance_code_index');
        $this->addIndexIfMissing('maintenance', ['fleetCode', 'date', 'deleted_at'], 'maintenance_fleet_date_deleted_index');

        $this->addIndexIfMissing('maintenance_detail', ['maintenanceCode'], 'maintenance_detail_maint_index');
        $this->addIndexIfMissing('maintenance_detail', ['itemCode'], 'maintenance_detail_item_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('fleet', 'fleet_code_index');
        $this->dropIndexIfExists('fleet', 'fleet_company_deleted_index');

        $this->dropIndexIfExists('order', 'order_fleet_date_deleted_index');
        $this->dropIndexIfExists('order', 'order_route_index');

        $this->dropIndexIfExists('route_detail', 'route_detail_route_index');
        $this->dropIndexIfExists('route_detail', 'route_detail_component_index');

        $this->dropIndexIfExists('invoice_detail', 'invoice_detail_order_index');
        $this->dropIndexIfExists('invoice_detail', 'invoice_detail_invoice_index');

        $this->dropIndexIfExists('order_payment', 'order_payment_order_index');

        $this->dropIndexIfExists('maintenance', 'maintenance_code_index');
        $this->dropIndexIfExists('maintenance', 'maintenance_fleet_date_deleted_index');

        $this->dropIndexIfExists('maintenance_detail', 'maintenance_detail_maint_index');
        $this->dropIndexIfExists('maintenance_detail', 'maintenance_detail_item_index');
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || Schema::hasIndex($table, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }
};
