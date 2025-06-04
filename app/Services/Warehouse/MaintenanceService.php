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

        // 2. Jika ada item digunakan
        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty']);

            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $itemCode = $filtered['itemCode'][$i];
                $requestedQty = (int) $filtered['qty'][$i];

                // Validasi jumlah tidak boleh nol atau negatif
                if ($requestedQty <= 0) {
                    throw new \Exception("Qty untuk item {$itemCode} tidak boleh 0.");
                }

                // 3. Validasi stok FIFO tersedia
                $totalAvailableQty = PurchaseDetail::where('itemCode', $itemCode)
                    ->where('status', 1)
                    ->selectRaw('SUM(receivedQty - COALESCE(qtyUsed, 0)) as available')
                    ->value('available') ?? 0;

                if ($totalAvailableQty < $requestedQty) {
                    throw new \Exception("Stok tidak cukup untuk item {$itemCode}. Tersedia: {$totalAvailableQty}, Diminta: {$requestedQty}");
                }

                // 4. Buat MaintenanceDetail terlebih dahulu
                $detail = $data->details()->create([
                    'code' => GenerateCode::generateCode('FMD', true),
                    'maintenanceCode' => $data->code,
                    'itemCode' => $itemCode,
                    'qty' => $requestedQty
                ]);

                // 5. Alokasikan FIFO dan simpan ke MaintenanceFifo
                $remainingQty = $requestedQty;
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

                // 6. Update stok keluar
                Stock::where('itemCode', $itemCode)->increment('stockOut', $requestedQty);

                // 7. Buat StockTransaction
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FPD', true),
                    'itemCode' => $itemCode,
                    'qty' => $requestedQty,
                    'transactionCode' => $detail->code,
                    'date' => $request->date,
                    'type' => 'OUT',
                    'transactionType' => 2
                ]);
            }
        }

        // 8. Log aktivitas pencatatan
        $this->logActivity($title, $data, 'Create');
    }




    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            // 'code' => $request->code,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode
        ]);


        dd($request->all());

        if (isset($request->itemCode)) {
            $itemCodes = $request->itemCode;
            $qtys = $request->qty;
            $originalQtys = $request->original_qty;
            $maintenanceCode = $request->code;

            $detailMap = [];
            $filtered = Arr::only($request->all(), ['itemCode', 'qty', 'maintenanceDetailCode']);


            // STEP 1: Rollback qtyUsed hanya untuk data yang digunakan di maintenance_fifo
            foreach ($itemCodes as $i => $itemCode) {
                $detailCode = $filtered['maintenanceDetailCode'][$i] ?? null;

                $md = MaintenanceDetail::where('code', $detailCode)
                    ->first();

                if ($md) {
                    $detailMap[$itemCode] = $md;

                    // Ambil semua fifo terkait detail ini
                    $fifos = MaintenanceFifo::where('maintenanceDetailCode', $md->code)->get();

                    foreach ($fifos as $fifo) {
                        PurchaseDetail::where('code', $fifo->purchaseDetailCode)
                            ->decrement('qtyUsed', $fifo->qty);
                    }

                    // Reset semua qty FIFO ke 0 (opsional jika kamu ingin audit)
                    MaintenanceFifo::where('maintenanceDetailCode', $md->code)
                        ->update(['qty' => 0]);

                    // Reset stockOut
                    Stock::where('itemCode', $itemCode)
                        ->decrement('stockOut', $md->qty);
                }
            }

            // Hapus semua FIFO yang qty-nya sudah 0
            MaintenanceFifo::where('qty', 0)->delete();

            // STEP 2: Alokasi ulang
            for ($i = 0; $i < count($itemCodes); $i++) {
                $itemCode = $itemCodes[$i];
                $qty = (int)$qtys[$i];
                $originalQty = (int)$originalQtys[$i];
                // $isLifo = $qty < $originalQty;

                if ($qty <= 0) {
                    throw new \Exception("Qty untuk item {$itemCode} tidak boleh 0.");
                }

                $detail = $detailMap[$itemCode] ?? null;

                if ($detail) {
                    $detail->update(['qty' => $qty]);
                } else {
                    $data = $this->getById($id);
                    $detail = $data->details()->create([
                        'code' => GenerateCode::generateCode('TMD', true),
                        'maintenanceCode' => $maintenanceCode,
                        'itemCode' => $itemCode,
                        'qty' => $qty
                    ]);
                }

                // Ambil batch dari purchase_detail
                $purchaseDetails = PurchaseDetail::where('itemCode', $itemCode)
                    ->where('status', 1)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $remainingQty = $qty;

                foreach ($purchaseDetails as $purchase) {
                    if ($remainingQty <= 0) break;

                    $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                    if ($availableQty <= 0) continue;

                    $useQty = min($remainingQty, $availableQty);

                    PurchaseDetail::where('code', $purchase->code)
                        ->increment('qtyUsed', $useQty);

                    MaintenanceFifo::create([
                        'code' => GenerateCode::generateCode('MF', true),
                        'maintenanceDetailCode' => $detail->code,
                        'purchaseDetailCode' => $purchase->code,
                        'qty' => $useQty
                    ]);

                    $remainingQty -= $useQty;
                }

                Stock::where('itemCode', $itemCode)
                    ->increment('stockOut', $qty);

                StockTransaction::updateOrCreate(
                    ['transactionCode' => $detail->code],
                    [
                        'itemCode' => $itemCode,
                        'qty' => $qty,
                        'date' => $request->date,
                        'type' => 'OUT',
                        'transactionType' => 2
                    ]
                );
            }
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }




    public function destroy($id, $title)
    {
        // Log data sebelum dihapus
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            // 1. Rollback FIFO
            $fifos = MaintenanceFifo::where('maintenanceDetailCode', $item->code)->get();
            foreach ($fifos as $fifo) {
                PurchaseDetail::where('code', $fifo->purchaseDetailCode)
                    ->decrement('qtyUsed', $fifo->qty);
            }

            // 2. Hapus data FIFO terkait
            MaintenanceFifo::where('maintenanceDetailCode', $item->code)->delete();

            // 3. Update stok keluar
            Stock::where('itemCode', $item->item->code)->decrement('stockOut', $item->qty);

            // 4. Hapus transaksi stok
            StockTransaction::where('transactionCode', $item->code)->delete();
        }

        // 5. Hapus detail maintenance
        $data->details()->delete();

        // 6. Hapus data maintenance utama
        $this->service->where('id', $id)->delete();
    }
}
