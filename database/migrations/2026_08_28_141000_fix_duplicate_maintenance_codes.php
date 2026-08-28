<?php

use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixDuplicateMaintenanceCodes extends Migration
{
    public function up()
    {
        $fixes = [
            [
                'id' => 'b3372fb2-d189-4534-b239-a0ff217be0c6',
                'old_code' => 'MNT-26072700001',
                'new_code' => 'MNT-26062700010',
                'detail_codes' => ['FMD260716141958688', 'FMD260716141958716'],
            ],
            [
                'id' => '603fb6e8-056d-43c4-895d-198f30fecf3c',
                'old_code' => 'MNT-26072700002',
                'new_code' => 'MNT-26062700011',
                'detail_codes' => ['FMD260716142024228', 'FMD260716142024254'],
            ],
            [
                'id' => '78551e78-a9f7-49ba-b067-231877b4bc0c',
                'old_code' => 'MNT-26072700003',
                'new_code' => 'MNT-26062700012',
                'detail_codes' => ['FMD260716142052585', 'FMD260716142052611'],
            ],
            [
                'id' => '6bf581d1-9129-4cb3-9f4b-b4de45b8c491',
                'old_code' => 'MNT-26072700004',
                'new_code' => 'MNT-26062700013',
                'detail_codes' => ['FMD260716153512389'],
            ],
            [
                'id' => 'da1144be-3ba6-45c0-80b9-81a01756eb64',
                'old_code' => 'MNT-26072700005',
                'new_code' => 'MNT-26062700014',
                'detail_codes' => ['FMD260828100856694', 'FMD260828100856661'],
            ],
        ];

        DB::transaction(function () use ($fixes) {
            foreach ($fixes as $fix) {
                // Update maintenance record code
                DB::table('maintenance')
                    ->where('id', $fix['id'])
                    ->update(['code' => $fix['new_code']]);

                // Update matching details to point to new maintenanceCode
                DB::table('maintenance_detail')
                    ->whereIn('code', $fix['detail_codes'])
                    ->update(['maintenanceCode' => $fix['new_code']]);

                // Update matching stock transactions
                DB::table('stock_transaction')
                    ->whereIn('transactionDetailCode', $fix['detail_codes'])
                    ->update(['transactionCode' => $fix['new_code']]);
            }

            // Recalculate grand_total for all maintenance records
            $maintenances = Maintenance::withTrashed()->get();
            foreach ($maintenances as $m) {
                $m->updateGrandTotal();
            }
        });
    }

    public function down()
    {
        // No down migration as this cleans up corrupt duplicate keys
    }
}
