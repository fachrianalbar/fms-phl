<?php

namespace App\Services\Warehouse;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;

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
        // 1. Simpan data maintenance utama
        $data = $this->service->create([
            'code' => $request->code,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode
        ]);

        // 2. Jika ada item yang digunakan dalam maintenance
        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty']);

            for ($i = 0; $i < count($request->itemCode); $i++) {
                $itemCode = $filtered['itemCode'][$i];
                $requestedQty = (int)$filtered['qty'][$i];

                // 3. Cek apakah stok cukup berdasarkan FIFO data
                $totalAvailableQty = PurchaseDetail::where('itemCode', $itemCode)
                    ->selectRaw('SUM(receivedQty - qtyUsed) as available')
                    ->value('available');

                if ($totalAvailableQty < $requestedQty) {
                    // Jika stok tidak cukup, batalkan dan kembalikan pesan error
                    return response()->json([
                        'message' => "Stok tidak cukup untuk item $itemCode. Tersedia: $totalAvailableQty, Diminta: $requestedQty"
                    ], 400);
                }

                // 4. Update FIFO PurchaseDetail: qtyUsed sesuai permintaan
                $remainingQty = $requestedQty;
                $purchases = PurchaseDetail::where('itemCode', $itemCode)
                    ->whereColumn('qtyUsed', '<', 'receivedQty')
                    ->orderBy('created_at')
                    ->get();

                foreach ($purchases as $purchase) {
                    if ($remainingQty <= 0) break;

                    $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                    $useQty = min($remainingQty, $availableQty);

                    $purchase->qtyUsed += $useQty;
                    $purchase->save();

                    $remainingQty -= $useQty;
                }

                // 5. Update stok global
                $stock = Stock::where('itemCode', $itemCode)->first();

                $stock->update([
                    'stockOut' => $stock->stockOut + $requestedQty
                ]);

                // 6. Buat MaintenanceDetail dan StockTransaction
                $code = $data->code;

                $md = MaintenanceDetail::where('itemCode', $itemCode)
                    ->whereHas('maintenance', function ($query) use ($code) {
                        $query->where('code', $code);
                    })
                    ->first();

                if (!$md) {
                    $detail = $data->details()->create([
                        'code' => GenerateCode::generateCode('TMD', true),
                        'maintenanceCode' => $code,
                        'itemCode' => $itemCode,
                        'qty' => $requestedQty
                    ]);

                    StockTransaction::create([
                        'code' => GenerateCode::generateCode('TPD', true),
                        'itemCode' => $itemCode,
                        'qty' => $requestedQty,
                        'transactionCode' => $detail->code,
                        'date' => $request->date,
                        'type' => 'OUT',
                        'transactionType' => 2
                    ]);
                } else {
                    $md->update([
                        'qty' => $requestedQty + $md->qty
                    ]);

                    $stockTransaction = StockTransaction::where('itemCode', $itemCode)
                        ->where('transactionCode', $md->code)
                        ->first();

                    if ($stockTransaction) {
                        $stockTransaction->update([
                            'qty' => $requestedQty + $stockTransaction->qty
                        ]);
                    }
                }
            }
        }

        // 7. Log aktivitas
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

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty', 'maintenanceDetailCode']);

            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $qty = (int)$filtered['qty'][$i];
                $itemCode = $filtered['itemCode'][$i];
                $detailCode = $filtered['maintenanceDetailCode'][$i] ?? null;

                if ($qty <= 0) {
                    throw new \Exception("Qty untuk item {$itemCode} tidak boleh 0.");
                }

                $stock = Stock::where('itemCode', $itemCode)->firstOrFail();

                if ($detailCode) {
                    // UPDATE DETAIL LAMA
                    $md = MaintenanceDetail::where('code', $detailCode)->firstOrFail();
                    $originalQty = $md->qty;
                    $oldItemCode = $md->itemCode;

                    // ROLLBACK FIFO: Ambil data lama dari maintenance_fifo
                    $oldFifos = MaintenanceFifo::where('maintenanceDetailCode', $detailCode)->get();
                    foreach ($oldFifos as $fifo) {
                        $purchase = PurchaseDetail::where('code', $fifo->purchaseDetailCode)->first();
                        if ($purchase) {
                            $purchase->qtyUsed -= $fifo->qty;
                            $purchase->save();
                        }
                    }

                    // Rollback stockOut
                    Stock::where('itemCode', $oldItemCode)->decrement('stockOut', $originalQty);

                    // Hapus maintenance_fifo lama
                    MaintenanceFifo::where('maintenanceDetailCode', $detailCode)->delete();

                    // Validasi stok
                    $available = PurchaseDetail::where('itemCode', $itemCode)
                        ->selectRaw('SUM(receivedQty - qtyUsed) as available')
                        ->value('available');

                    if ($qty > $available) {
                        throw new \Exception("Qty untuk item {$itemCode} melebihi stok tersedia (FIFO) = {$available}.");
                    }

                    // Update MaintenanceDetail
                    $md->update([
                        'itemCode' => $itemCode,
                        'qty' => $qty,
                        'maintenanceCode' => $request->code
                    ]);

                    // ALOKASI FIFO BARU
                    $remainingQty = $qty;
                    $fifoPurchases = PurchaseDetail::where('itemCode', $itemCode)
                        ->whereColumn('qtyUsed', '<', 'receivedQty')
                        ->orderBy('created_at')
                        ->get();

                    foreach ($fifoPurchases as $purchase) {
                        if ($remainingQty <= 0) break;

                        $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                        $useQty = min($remainingQty, $availableQty);

                        $purchase->qtyUsed += $useQty;
                        $purchase->save();

                        MaintenanceFifo::create([
                            'code' => GenerateCode::generateCode('MF', true),
                            'maintenanceDetailCode' => $detailCode,
                            'purchaseDetailCode' => $purchase->code,
                            'qty' => $useQty
                        ]);

                        $remainingQty -= $useQty;
                    }

                    Stock::where('itemCode', $itemCode)->increment('stockOut', $qty);

                    StockTransaction::where('transactionCode', $detailCode)->update([
                        'itemCode' => $itemCode,
                        'qty' => $qty
                    ]);
                } else {
                    // TAMBAH DETAIL BARU
                    $available = PurchaseDetail::where('itemCode', $itemCode)
                        ->selectRaw('SUM(receivedQty - qtyUsed) as available')
                        ->value('available');

                    if ($qty > $available) {
                        throw new \Exception("Qty untuk item {$itemCode} melebihi stok tersedia (FIFO) = {$available}.");
                    }

                    $data = $this->getById($id);
                    $code = $data->code;

                    $detail = $data->details()->create([
                        'code' => GenerateCode::generateCode('TMD', true),
                        'maintenanceCode' => $code,
                        'itemCode' => $itemCode,
                        'qty' => $qty
                    ]);

                    // ALOKASI FIFO BARU
                    $remainingQty = $qty;
                    $fifoPurchases = PurchaseDetail::where('itemCode', $itemCode)
                        ->whereColumn('qtyUsed', '<', 'receivedQty')
                        ->orderBy('created_at')
                        ->get();

                    foreach ($fifoPurchases as $purchase) {
                        if ($remainingQty <= 0) break;

                        $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                        $useQty = min($remainingQty, $availableQty);

                        $purchase->qtyUsed += $useQty;
                        $purchase->save();

                        MaintenanceFifo::create([
                            'code' => GenerateCode::generateCode('MF', true),
                            'maintenanceDetailCode' => $detail->code,
                            'purchaseDetailCode' => $purchase->code,
                            'qty' => $useQty
                        ]);

                        $remainingQty -= $useQty;
                    }

                    Stock::where('itemCode', $itemCode)->increment('stockOut', $qty);

                    StockTransaction::create([
                        'code' => GenerateCode::generateCode('TPD', true),
                        'itemCode' => $itemCode,
                        'qty' => $qty,
                        'transactionCode' => $detail->code,
                        'date' => $request->date,
                        'type' => 'OUT',
                        'transactionType' => 2
                    ]);
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

            StockTransaction::where('transactionCode', $item->code)->delete();
        }

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
