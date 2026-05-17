<?php

namespace App\Services\Warehouse;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MaintenanceService
{
    use LogActivity;

    protected Maintenance $service;

    public function __construct(Maintenance $maintenance)
    {
        $this->service = $maintenance;
    }

    protected function isJasaItem(string $itemCode): bool
    {
        return Item::query()->where('code', $itemCode)->value('type') === Item::TYPE_JASA;
    }

    public function findAll()
    {
        return $this->service->with([
            'fleet',
            'details',
            'details.item',
        ])->latest();
    }

    public function datatable()
    {
        return $this->service->with([
            'fleet',
            'warehouse',
            'details',
            'details.item',
        ])->where('status', 0)->orderBy('created_at', 'desc');
    }

    public function getById(string $id)
    {
        return $this->service->query()->where('id', $id)->first();
    }

    public function store(Request $request, string $title)
    {
        $warehouseCode = $request->warehouseCode ?? Warehouse::query()->first()->code;

        $code = $request->code;
        $data = null;
        $retryCount = 0;
        $maxRetries = 10;

        while ($retryCount < $maxRetries) {
            try {
                // 1. Simpan data maintenance utama
                $data = $this->service->create([
                    'code' => $code,
                    'date' => $request->date,
                    'time' => $request->time,
                    'fleetCode' => $request->fleetCode,
                    'warehouseCode' => $warehouseCode,
                ]);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                    $retryCount++;
                    $code = GenerateCode::generateCodeAscDate(
                        'MNT',
                        Maintenance::class,
                        'date',
                        $request->date
                    );
                } else {
                    throw $e;
                }
            }
        }

        if (! $data) {
            throw new \Exception("Gagal menyimpan data karena duplikasi kode setelah mencoba {$maxRetries} kali.");
        }

        // 2. Jika ada item digunakan
        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['itemCode', 'qty']);

            for ($i = 0; $i < count($filtered['itemCode']); $i++) {
                $itemCode = $filtered['itemCode'][$i];
                $requestedQty = (float) $filtered['qty'][$i];
                $isJasa = $this->isJasaItem($itemCode);

                // Validasi jumlah tidak boleh nol atau negatif
                if ($requestedQty <= 0) {
                    throw new \Exception("Qty untuk item {$itemCode} tidak boleh 0.");
                }

                if (! $isJasa) {
                    // 3. Validasi stok tersedia dari stock_transaction berdasarkan warehouse
                    $availableStock = StockTransaction::query()->where('itemCode', $itemCode)
                        ->where('warehouseCode', $warehouseCode)
                        ->selectRaw('SUM(qtyIn) - SUM(qtyOut) as totalStock')
                        ->value('totalStock') ?? 0;

                    if ($availableStock < $requestedQty) {
                        throw new \Exception("Stok tidak cukup untuk item {$itemCode} di gudang {$warehouseCode}. Tersedia: {$availableStock}, Diminta: {$requestedQty}");
                    }
                }

                // 4. Buat MaintenanceDetail terlebih dahulu
                $detail = $data->details()->create([
                    'code' => GenerateCode::generateCode('FMD', true),
                    'maintenanceCode' => $data->code,
                    'itemCode' => $itemCode,
                    'qty' => $requestedQty,
                ]);

                if ($isJasa) {
                    continue;
                }

                // 5. Alokasikan FIFO dan simpan ke MaintenanceFifo
                $remainingQty = $requestedQty;
                $fifoPurchases = PurchaseDetail::query()->where('itemCode', $itemCode)
                    ->whereColumn('qtyUsed', '<', 'receivedQty')
                    ->orderBy('created_at')
                    ->get();

                /** @var \App\Models\Purchasing\PurchaseDetail $purchase */
                foreach ($fifoPurchases as $purchase) {
                    if ($remainingQty <= 0) {
                        break;
                    }

                    $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                    $useQty = min($remainingQty, $availableQty);

                    $purchase->qtyUsed += $useQty;
                    $purchase->save();

                    MaintenanceFifo::create([
                        'code' => GenerateCode::generateCode('MF', true),
                        'maintenanceDetailCode' => $detail->code,
                        'purchaseDetailCode' => $purchase->code,
                        'qty' => $useQty,
                    ]);

                    $remainingQty -= $useQty;
                }

                // 6. Update stok keluar
                Stock::query()->where('itemCode', $itemCode)->increment('stockOut', $requestedQty);

                // 7. Buat StockTransaction
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $itemCode,
                    'qtyIn' => 0,
                    'qtyOut' => $requestedQty,
                    'transactionCode' => $data->code,
                    'transactionDetailCode' => $detail->code,
                    'date' => $request->date . ' ' . $request->time,
                    'transactionType' => 'OUT',
                    'warehouseCode' => $warehouseCode,
                ]);
            }
        }

        // 8. Log aktivitas pencatatan
        $this->logActivity($title, $data, 'Create');
    }

    public function update(Request $request, string $id, string $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->query()->where('id', $id)->update([
            // 'code' => $request->code,
            'date' => $request->date,
            'time' => $request->time,
            'fleetCode' => $request->fleetCode,
        ]);

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

                $md = MaintenanceDetail::query()->where('code', $detailCode)
                    ->first();

                if ($md) {
                    $detailMap[$itemCode] = $md;
                    $shouldRollbackInventory = optional($md->item)->type !== Item::TYPE_JASA;

                    if ($shouldRollbackInventory) {
                        // Ambil semua fifo terkait detail ini
                        $fifos = MaintenanceFifo::query()->where('maintenanceDetailCode', $md->code)->get();

                        foreach ($fifos as $fifo) {
                            PurchaseDetail::query()->where('code', $fifo->purchaseDetailCode)
                                ->decrement('qtyUsed', $fifo->qty);
                        }

                        // Reset semua qty FIFO ke 0 (opsional jika kamu ingin audit)
                        MaintenanceFifo::query()->where('maintenanceDetailCode', $md->code)
                            ->update(['qty' => 0]);

                        // Reset stockOut
                        Stock::query()->where('itemCode', $itemCode)
                            ->decrement('stockOut', $md->qty);
                    }
                }
            }

            // Hapus semua FIFO yang qty-nya sudah 0
            MaintenanceFifo::query()->where('qty', 0)->delete();

            // STEP 2: Alokasi ulang
            for ($i = 0; $i < count($itemCodes); $i++) {
                $itemCode = $itemCodes[$i];
                $qty = (float) $qtys[$i];
                $originalQty = (float) $originalQtys[$i];
                $isJasa = $this->isJasaItem($itemCode);
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
                        'qty' => $qty,
                    ]);
                }

                if ($isJasa) {
                    continue;
                }

                // Ambil batch dari purchase_detail
                $purchaseDetails = PurchaseDetail::query()->where('itemCode', $itemCode)
                    ->where('status', 1)
                    ->orderBy('created_at', 'asc')
                    ->get();

                $remainingQty = $qty;

                foreach ($purchaseDetails as $purchase) {
                    if ($remainingQty <= 0) {
                        break;
                    }

                    $availableQty = $purchase->receivedQty - $purchase->qtyUsed;
                    if ($availableQty <= 0) {
                        continue;
                    }

                    $useQty = min($remainingQty, $availableQty);

                    PurchaseDetail::query()->where('code', $purchase->code)
                        ->increment('qtyUsed', $useQty);

                    MaintenanceFifo::create([
                        'code' => GenerateCode::generateCode('MF', true),
                        'maintenanceDetailCode' => $detail->code,
                        'purchaseDetailCode' => $purchase->code,
                        'qty' => $useQty,
                    ]);

                    $remainingQty -= $useQty;
                }

                Stock::query()->where('itemCode', $itemCode)
                    ->increment('stockOut', $qty);

                StockTransaction::updateOrCreate(
                    ['transactionDetailCode' => $detail->code],
                    [
                        'itemCode' => $itemCode,
                        'qtyIn' => 0,
                        'qtyOut' => $qty,
                        'date' => $request->date . ' ' . $request->time,
                        'transactionType' => 'OUT',
                    ]
                );
            }
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy(string $id, string $title)
    {
        // Log data sebelum dihapus
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            $isJasa = optional($item->item)->type === Item::TYPE_JASA;

            if ($isJasa) {
                continue;
            }

            // 1. Rollback FIFO
            $fifos = MaintenanceFifo::query()->where('maintenanceDetailCode', $item->code)->get();
            foreach ($fifos as $fifo) {
                PurchaseDetail::query()->where('code', $fifo->purchaseDetailCode)
                    ->decrement('qtyUsed', $fifo->qty);
            }

            // 2. Hapus data FIFO terkait
            MaintenanceFifo::query()->where('maintenanceDetailCode', $item->code)->delete();

            // 3. Update stok keluar
            Stock::query()->where('itemCode', $item->item->code)->decrement('stockOut', $item->qty);

            // 4. Hapus transaksi stok
            StockTransaction::query()->where('transactionDetailCode', $item->code)->delete();
        }

        // 5. Hapus detail maintenance
        $data->details()->delete();

        // 6. Update code agar tidak bentrok dengan unique constraint, lalu hapus data maintenance utama
        $data->update([
            'code' => $data->code . '-DEL-' . str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT)
        ]);
        $this->service->query()->where('id', $id)->delete();
    }
}
