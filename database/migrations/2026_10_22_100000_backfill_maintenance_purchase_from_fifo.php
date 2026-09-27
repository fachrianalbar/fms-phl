<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_purchase') || ! Schema::hasTable('maintenance_fifo')) {
            return;
        }

        $pairs = DB::table('maintenance_fifo')
            ->join('maintenance_detail', 'maintenance_detail.code', '=', 'maintenance_fifo.maintenanceDetailCode')
            ->join('maintenance', 'maintenance.code', '=', 'maintenance_detail.maintenanceCode')
            ->join('purchase_detail', 'purchase_detail.code', '=', 'maintenance_fifo.purchaseDetailCode')
            ->join('purchase', 'purchase.code', '=', 'purchase_detail.purchaseCode')
            ->whereNull('maintenance.deleted_at')
            ->whereNull('purchase.deleted_at')
            ->select('maintenance.id as maintenance_id', 'purchase.id as purchase_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            $exists = DB::table('maintenance_purchase')
                ->where('maintenance_id', $pair->maintenance_id)
                ->where('purchase_id', $pair->purchase_id)
                ->exists();

            if (! $exists) {
                DB::table('maintenance_purchase')->insert([
                    'id' => (string) Str::uuid(),
                    'maintenance_id' => $pair->maintenance_id,
                    'purchase_id' => $pair->purchase_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback to preserve integrity of maintenance_purchase data
    }
};
