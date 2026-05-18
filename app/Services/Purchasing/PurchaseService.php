<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Services\Helper\StockManagementService;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PurchaseService
{
    use LogActivity;

    protected Purchase $service;

    protected StockManagementService $stockManagement;

    public function __construct(Purchase $purchase, StockManagementService $stockManagement)
    {
        $this->service = $purchase;
        $this->stockManagement = $stockManagement;
    }

    public function findAll()
    {
        return $this->service->query()->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->query()->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->orderBy('time', 'desc');
    }

    public function getById(string $id)
    {
        return $this->service->query()->where('id', $id)->with([
            'details',
            'details.item',
            'purchaseStatus',
        ])->first();
    }

    public function store(Request $request, string $title)
    {
        $warehouse = Warehouse::query()->first();
        $data = $this->service->create([
            'code' => $request->code,
            'supplierCode' => $request->supplierCode,
            'warehouseCode' => $warehouse->code,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->dueDate,
        ]);

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'price', 'description']);

            for ($i = 0; $i < count($request->itemCode); $i++) {

                $price = (int) str_replace('.', '', $filtered['price'][$i]);

                // Update Item master price
                Item::query()->where('code', $filtered['itemCode'][$i])->update([
                    'price' => $price,
                ]);

                $code = $data->code;

                $pd = PurchaseDetail::query()->where('itemCode', $filtered['itemCode'][$i])
                    ->whereHas('purchase', function ($query) use ($code) {
                        $query->where('code', $code);
                    })
                    ->first();

                if (! $pd) {
                    $data->details()->create([
                        'code' => GenerateCode::generateCode('FPD', true),
                        'itemCode' => $filtered['itemCode'][$i],
                        'qty' => $filtered['qty'][$i],
                        'receivedQty' => $filtered['qty'][$i],
                        'status' => 1,
                        'purchaseCode' => $request->code,
                        'price' => $price,
                        'description' => $filtered['description'][$i] ?? null,
                    ]);
                } else {
                    $pd->update([
                        'qty' => $filtered['qty'][$i] + $pd->qty,
                        'description' => $filtered['description'][$i] ?? $pd->description,
                    ]);
                }

                $pd = PurchaseDetail::query()->where('itemCode', $filtered['itemCode'][$i])
                    ->whereHas('purchase', function ($query) use ($code) {
                        $query->where('code', $code);
                    })
                    ->first();

                $this->stockManagement->processStockIn(
                    $filtered['itemCode'][$i],
                    $warehouse->code,
                    $filtered['qty'][$i],
                    $request->code, // transactionCode = Purchase code
                    $pd->code, // transactionDetailCode = PurchaseDetail code
                    $request->date . ' ' . $request->time,
                    'IN'
                );

                // $this->logActivity('Purchase Detail', $purchaseDetail, 'Create');
            }
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update(Request $request, string $id, string $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        // Get purchase data first for the code
        $purchaseData = $this->getById($id);
        $purchaseCode = $purchaseData->code;

        // Rollback all stock and stock transactions for this purchase first
        $stockTransactions = StockTransaction::withTrashed()->where('transactionCode', $purchaseCode)->get();

        foreach ($stockTransactions as $transaction) {
            Stock::withTrashed()->where('itemCode', $transaction->itemCode)->decrement('stockIn', $transaction->qtyIn);
        }

        // Delete semua StockTransaction untuk purchase ini
        StockTransaction::withTrashed()->where('transactionCode', $purchaseCode)->forceDelete();

        $this->service->query()->where('id', $id)->update([
            // 'code' => $request->code,
            'supplierCode' => $request->supplierCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->dueDate,
        ]);

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price', 'description']);

            for ($i = 0; $i < count($request->itemCode); $i++) {
                $price = (int) str_replace('.', '', $filtered['price'][$i]);

                Item::query()->where('code', $filtered['itemCode'][$i])->update([
                    'price' => $price,
                ]);

                $detailCode = null;

                // Check if purchaseDetailCode exists AND is not empty (existing item)
                if (isset($filtered['purchaseDetailCode'][$i]) && ! empty($filtered['purchaseDetailCode'][$i])) {
                    $pd = PurchaseDetail::query()->where('code', $filtered['purchaseDetailCode'][$i])->first();
                    if ($pd) {
                        $pd->update([
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'receivedQty' => $filtered['qty'][$i],
                            'price' => $price,
                            'description' => $filtered['description'][$i] ?? null,
                        ]);
                        $detailCode = $pd->code;
                    }
                }

                if (! $detailCode) {
                    // Check if duplicate item code exists in the current purchase
                    $pd = PurchaseDetail::query()->where('itemCode', $filtered['itemCode'][$i])
                        ->where('purchaseCode', $purchaseCode)
                        ->first();
                    
                    if ($pd) {
                        $pd->update([
                            'qty' => $pd->qty + $filtered['qty'][$i],
                            'receivedQty' => $pd->receivedQty + $filtered['qty'][$i],
                            'description' => $filtered['description'][$i] ?? $pd->description,
                        ]);
                        $detailCode = $pd->code;
                    } else {
                        $detail = PurchaseDetail::create([
                            'code' => GenerateCode::generateCode('FPD', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'receivedQty' => $filtered['qty'][$i],
                            'status' => 1,
                            'purchaseCode' => $purchaseCode,
                            'price' => $price,
                            'description' => $filtered['description'][$i] ?? null,
                        ]);
                        $detailCode = $detail->code;
                    }
                }

                // Add stock and create StockTransaction
                $stock = Stock::query()->where('itemCode', $filtered['itemCode'][$i])->first();

                if ($stock) {
                    $stock->increment('stockIn', $filtered['qty'][$i]);
                } else {
                    Stock::create([
                        'code' => GenerateCode::generateCode('TSTC', true),
                        'itemCode' => $filtered['itemCode'][$i],
                        'stockIn' => $filtered['qty'][$i],
                        'stockOut' => 0,
                    ]);
                }

                // Find existing stock transaction (shouldn't be any, but just in case)
                $stockTransaction = StockTransaction::query()
                    ->where('transactionCode', $purchaseCode)
                    ->where('transactionDetailCode', $detailCode)
                    ->first();

                if ($stockTransaction) {
                    $stockTransaction->increment('qtyIn', $filtered['qty'][$i]);
                } else {
                    StockTransaction::create([
                        'code' => GenerateCode::generateCode('FST', true),
                        'itemCode' => $filtered['itemCode'][$i],
                        'qtyIn' => $filtered['qty'][$i],
                        'qtyOut' => 0,
                        'transactionCode' => $purchaseCode,
                        'transactionDetailCode' => $detailCode,
                        'date' => $request->date . ' ' . $request->time,
                        'transactionType' => 'IN',
                    ]);
                }
            }

            // Delete PurchaseDetail yang tidak ada di request
            $sentDetailCodes = array_filter($filtered['purchaseDetailCode'] ?? []);
            $deletedDetails = PurchaseDetail::where('purchaseCode', $purchaseCode)
                ->whereNotIn('code', $sentDetailCodes)
                ->get();

            foreach ($deletedDetails as $deleted) {
                $deleted->delete();
            }
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy(string $id, string $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        // Rollback stock: kurangi stockIn berdasarkan total qtyIn dari StockTransaction
        $stockTransactions = StockTransaction::withTrashed()->where('transactionCode', $data->code)->get();

        foreach ($stockTransactions as $transaction) {
            Stock::withTrashed()->where('itemCode', $transaction->itemCode)->decrement('stockIn', $transaction->qtyIn);
        }

        // Delete semua StockTransaction untuk purchase ini
        StockTransaction::withTrashed()->where('transactionCode', $data->code)->forceDelete();

        $data->details()->delete();

        // Update code agar tidak bentrok dengan unique constraint
        $data->update([
            'code' => $data->code . '-DEL-' . str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT)
        ]);
        $this->service->query()->where('id', $id)->delete();
    }
}
