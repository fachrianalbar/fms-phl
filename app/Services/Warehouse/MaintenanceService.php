<?php

namespace App\Services\Warehouse;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class MaintenanceService
{
    use LogActivity;

    protected $service;

    public function __construct(Maintenance $maintenance)
    {
        $this->service = $maintenance;
    }

    public function findAll()
    {
        return $this->service->with([
            'fleet',
            'details',
            'details.item'
        ])->latest();
    }


    public function datatable()
    {
        return $this->service->with([
            'fleet',
            'details',
            'details.item'
        ])->where('status', 0)->orderBy('created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'code' => $request->code,
            // 'maintenanceCode' => $request->maintenanceCode,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode
        ]);

        if (env('DB_PREFIX') == 'el_') {
            DB::connection('mysql2')->table('maintenance')->insert([
                'id' => $data->id,
                'code' => $request->code,
                // 'maintenanceCode' => $request->maintenanceCode,
                'date' => $request->date,
                'time' => $request->time,
                'fleetCode' => $request->fleetCode,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }


        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty']);

            for ($i = 0; $i < count($request->itemCode); $i++) {
                $stock = Stock::where('itemCode', $filtered['itemCode'][$i])->first();

                if ($filtered['qty'][$i] > $stock->stockIn) {
                    return;
                }

                $stock->update([
                    // 'stockIn' => $stock->stockIn - (int)$filtered['qty'][$i],
                    'stockOut' => $stock->stockOut + (int)$filtered['qty'][$i]
                ]);

                $code = $data->code;

                $md = MaintenanceDetail::where('itemCode', $filtered['itemCode'][$i])
                    ->whereHas('maintenance', function ($query) use ($code) {
                        $query->where('code', $code);
                    })
                    ->first();


                if (!$md) {
                    $detail = $data->details()->create([
                        'code' => GenerateCode::generateCode('TMD', true),
                        'maintenanceCode' => $code,
                        'itemCode' => $filtered['itemCode'][$i],
                        'qty' => $filtered['qty'][$i]
                    ]);

                    if (env('DB_PREFIX') == 'el_') {
                        DB::connection('mysql2')->table('maintenance_detail')->insert([
                            'id' => $detail->id,
                            'code' => $detail->code,
                            'maintenanceCode' => $code,
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    StockTransaction::create([
                        'code' => GenerateCode::generateCode('TPD', true),
                        'itemCode' => $filtered['itemCode'][$i],
                        'qty' => $filtered['qty'][$i],
                        'transactionCode' => $detail->code,
                        'date' => $request->date,
                        'type' => 'OUT'
                    ]);
                } else {
                    if (env('DB_PREFIX') == 'el_') {
                        DB::connection('mysql2')->table('maintenance_detail')->where('code', $md->code)->update([
                            'qty' => $filtered['qty'][$i]
                        ]);
                    }

                    $md->update([
                        'qty' => $filtered['qty'][$i] + $md->qty
                    ]);

                    $stockTransaction = StockTransaction::where('itemCode', $filtered['itemCode'][$i])->where('transactionCode', $md->code)->first();

                    if ($stockTransaction) {
                        $stockTransaction->update([
                            'qty' => $filtered['qty'][$i] + $stockTransaction->qty
                        ]);
                    }
                }
            }
        }


        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'code' => $request->code,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode
        ]);

        if (env('DB_PREFIX') == 'el_') {
            DB::connection('mysql2')->table('maintenance')->where('id', $id)->update([
                'code' => $request->code,
                'date' => $request->date,
                'time' => $request->time,
                'fleetCode' => $request->fleetCode
            ]);
        }


        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty', 'maintenanceDetailCode']);

            for ($i = 0; $i < count($request->itemCode); $i++) {

                if (isset($filtered['maintenanceDetailCode'][$i])) {

                    $md = MaintenanceDetail::where('code', $filtered['maintenanceDetailCode'][$i])->first();
                    $stock = Stock::where('itemCode', $md->itemCode)->first();

                    if ($filtered['qty'][$i] > $stock->stockIn) {
                        return;
                    }

                    if ($filtered['itemCode'][$i] != $md->itemCode) {
                        Stock::where('itemCode', $md->itemCode)->update([
                            // 'stockIn' => $stock->stockIn + $md->qty,
                            'stockOut' => $stock->stockOut - $md->qty
                        ]);


                        MaintenanceDetail::where('code', $filtered['maintenanceDetailCode'][$i])->update([
                            'maintenanceCode' => $request->code,
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i]
                        ]);

                        if (env('DB_PREFIX') == 'el_') {
                            DB::connection('mysql2')->table('maintenance_detail')->where('code', $filtered['maintenanceDetailCode'][$i])->update([
                                'maintenanceCode' => $request->code,
                                'itemCode' => $filtered['itemCode'][$i],
                                'qty' => $filtered['qty'][$i]
                            ]);
                        }

                        $md = MaintenanceDetail::where('code', $filtered['maintenanceDetailCode'][$i])->first();
                        $stock = Stock::where('itemCode', $md->itemCode)->first();

                        Stock::where('itemCode', $md->itemCode)->update([
                            // 'stockIn' => $stock->stockIn - $filtered['qty'][$i],
                            'stockOut' => $stock->stockOut + $filtered['qty'][$i]
                        ]);

                        StockTransaction::where('transactionCode', $filtered['maintenanceDetailCode'][$i])->update([
                            'qty' => $filtered['qty'][$i],
                            'itemCode' => $filtered['itemCode'][$i],
                        ]);
                    } else {
                        $stock = Stock::where('itemCode', $md->itemCode)->first();

                        Stock::where('itemCode', $md->itemCode)->update([
                            // 'stockIn' => $stock->stockIn + $md->qty,
                            'stockOut' => $stock->stockOut - $md->qty
                        ]);

                        $stock = Stock::where('itemCode', $md->itemCode)->first();

                        Stock::where('itemCode', $md->itemCode)->update([
                            // 'stockIn' => $stock->stockIn - $filtered['qty'][$i],
                            'stockOut' => $stock->stockOut + $filtered['qty'][$i]
                        ]);

                        if (env('DB_PREFIX') == 'el_') {
                            DB::connection('mysql2')->table('maintenance_detail')->where('code', $md->code)->update([
                                'qty' => $filtered['qty'][$i]
                            ]);
                        }

                        $md->update([
                            'qty' => $filtered['qty'][$i]
                        ]);

                        StockTransaction::where('transactionCode', $filtered['maintenanceDetailCode'][$i])->update([
                            'qty' => $filtered['qty'][$i]
                        ]);
                    }
                } else {
                    $data = $this->getById($id);
                    $code = $data->code;

                    $md = MaintenanceDetail::where('itemCode', $filtered['itemCode'][$i])
                        ->whereHas('maintenance', function ($query) use ($code) {
                            $query->where('code', $code);
                        })
                        ->first();

                    $stock = Stock::where('itemCode', $filtered['itemCode'][$i])->first();

                    Stock::where('itemCode', $filtered['itemCode'][$i])->update([
                        // 'stockIn' => $stock->stockIn - $filtered['qty'][$i],
                        'stockOut' => $stock->stockOut + $filtered['qty'][$i]
                    ]);

                    if (!$md) {
                        $detail = $data->details()->create([
                            'code' => GenerateCode::generateCode('TMD', true),
                            'maintenanceCode' => $code,
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i]
                        ]);

                        if (env('DB_PREFIX') == 'el_') {
                            DB::connection('mysql2')->table('maintenance_detail')->insert([
                                'id' => $detail->id,
                                'code' => $detail->code,
                                'maintenanceCode' => $code,
                                'itemCode' => $filtered['itemCode'][$i],
                                'qty' => $filtered['qty'][$i],
                                'status' => 1,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        StockTransaction::create([
                            'code' => GenerateCode::generateCode('TPD', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'transactionCode' => $detail->code,
                            'date' => $request->date,
                            'type' => 'OUT'
                        ]);
                    } else {
                        if (env('DB_PREFIX') == 'el_') {
                            DB::connection('mysql2')->table('maintenance_detail')->where('code', $md->code)->update([
                                'qty' => $filtered['qty'][$i]
                            ]);
                        }

                        $md->update([
                            'qty' => $filtered['qty'][$i]
                        ]);

                        $stockTransaction = StockTransaction::where('itemCode', $filtered['itemCode'][$i])->where('transactionCode', $md->code)->first();

                        if ($stockTransaction) {
                            $stockTransaction->update([
                                'qty' => $filtered['qty'][$i] + $stockTransaction->qty
                            ]);
                        }
                    }
                }
            }
        }


        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            // Stock::where('itemCode', $item->item->code)->increment('stockIn', $item->qty);

            $stock = Stock::where('itemCode', $item->item->code)->first();

            Stock::where('itemCode', $item->item->code)->update([
                'stockOut' => $stock->stockOut - $item->qty
            ]);

            if (env('DB_PREFIX') == 'el_') {
                DB::connection('mysql2')->table('maintenance_detail')->where('code', $item->code)->update([
                    'deleted_at' => now(),
                ]);
            }

            StockTransaction::where('transactionCode', $item->code)->delete();
        }

        if (env('DB_PREFIX') == 'el_') {
            DB::connection('mysql2')->table('maintenance')->where('id', $id)->update([
                'deleted_at' => now(),
            ]);
        }

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
