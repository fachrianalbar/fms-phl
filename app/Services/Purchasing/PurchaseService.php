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
use Illuminate\Support\Arr;

class PurchaseService
{
    use LogActivity;

    protected $service;

    protected $stockManagement;

    public function __construct(Purchase $purchase, StockManagementService $stockManagement)
    {
        $this->service = $purchase;
        $this->stockManagement = $stockManagement;
    }

    public function findAll()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->orderBy('time', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details',
            'details.item',
            'purchaseStatus',
        ])->first();
    }

    public function store($request, $title)
    {
        $warehouse = Warehouse::first();
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

                $code = $data->code;

                $pd = PurchaseDetail::where('itemCode', $filtered['itemCode'][$i])
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

                $pd = PurchaseDetail::where('itemCode', $filtered['itemCode'][$i])
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
                    Carbon::now(),
                    'IN'
                );

                // $this->logActivity('Purchase Detail', $purchaseDetail, 'Create');
            }
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        // Get purchase data first for the code
        $purchaseData = $this->getById($id);
        $purchaseCode = $purchaseData->code;

        $this->service->where('id', $id)->update([
            // 'code' => $request->code,
            'supplierCode' => $request->supplierCode,
            'date' => $request->date,
            'time' => $request->time,
            'dueDate' => $request->dueDate,
        ]);

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price', 'description']);

            for ($i = 0; $i < count($request->itemCode); $i++) {

                // Check if purchaseDetailCode exists AND is not empty (existing item)
                if (isset($filtered['purchaseDetailCode'][$i]) && !empty($filtered['purchaseDetailCode'][$i])) {
                    $pd = PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->first();

                    $stock = Stock::where('itemCode', $pd->item->code)->first();

                    $price = (int) str_replace('.', '', $filtered['price'][$i]);


                    Item::where('code', $filtered['itemCode'][$i])->update([
                        'price' => $price,
                    ]);

                    if ($pd->item->code != $filtered['itemCode'][$i]) {

                        if (isset($stock)) {
                            // decrement
                            Stock::where('itemCode', $pd->item->code)->update([
                                'stockIn' => $stock->stockIn - $pd->qty,
                            ]);
                        }

                        PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->update([
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'purchaseCode' => $purchaseCode,
                            'price' => $price,
                            'description' => $filtered['description'][$i] ?? null,
                        ]);

                        $pd = PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->first();

                        $stock = Stock::where('itemCode', $pd->item->code)->first();

                        if (! $stock) {
                            Stock::create([
                                'code' => GenerateCode::generateCode('TSTC', true),
                                'itemCode' => $filtered['itemCode'][$i],
                                'stockIn' => $filtered['qty'][$i],
                                'stockOut' => 0,
                            ]);
                        }

                        StockTransaction::where('transactionDetailCode', $filtered['purchaseDetailCode'][$i])->update([
                            'qtyIn' => $filtered['qty'][$i],
                            'itemCode' => $filtered['itemCode'][$i],
                        ]);
                    } else {
                        // Same item, only qty changed
                        $oldQty = $pd->qty;
                        $newQty = $filtered['qty'][$i];
                        $qtyDifference = $newQty - $oldQty;

                        $stock = Stock::where('itemCode', $pd->item->code)->first();

                        if ($stock) {
                            // Update stock with the difference
                            Stock::where('itemCode', $pd->item->code)->update([
                                'stockIn' => $stock->stockIn + $qtyDifference,
                            ]);
                        } else {
                            Stock::create([
                                'code' => GenerateCode::generateCode('TSTC', true),
                                'itemCode' => $filtered['itemCode'][$i],
                                'stockIn' => $newQty,
                                'stockOut' => 0,
                            ]);
                        }

                        $pd->update([
                            'qty' => $newQty,
                            'price' => $price,
                            'description' => $filtered['description'][$i] ?? $pd->description,
                        ]);

                        StockTransaction::where('transactionDetailCode', $filtered['purchaseDetailCode'][$i])->update([
                            'qtyIn' => $newQty,
                        ]);
                    }
                } else {
                    $checkItem = Stock::where('itemCode', $filtered['itemCode'][$i])->first();

                    $price = (int) str_replace('.', '', $filtered['price'][$i]);

                    Item::where('code', $filtered['itemCode'][$i])->update([
                        'price' => $price,
                    ]);

                    if ($checkItem) {
                        Stock::where('itemCode', $filtered['itemCode'][$i])->update([
                            'stockIn' => $filtered['qty'][$i] + $checkItem->stockIn,
                        ]);
                    } else {
                        Stock::create([
                            'code' => GenerateCode::generateCode('TSTC', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'stockIn' => $filtered['qty'][$i],
                            'stockOut' => 0,
                        ]);
                    }

                    // Check if this item already exists in this purchase (duplicate item)
                    $pd = PurchaseDetail::where('itemCode', $filtered['itemCode'][$i])
                        ->whereHas('purchase', function ($query) use ($purchaseCode) {
                            $query->where('code', $purchaseCode);
                        })
                        ->first();

                    if (! $pd) {
                        $detail = PurchaseDetail::create([
                            'code' => GenerateCode::generateCode('FPD', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'purchaseCode' => $purchaseCode,
                            'price' => $price,
                            'description' => $filtered['description'][$i] ?? null,
                        ]);

                        StockTransaction::create([
                            'code' => GenerateCode::generateCode('FST', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qtyIn' => $filtered['qty'][$i],
                            'qtyOut' => 0,
                            'transactionCode' => $purchaseCode,
                            'transactionDetailCode' => $detail->code,
                            'date' => Carbon::now(),
                            'transactionType' => 'IN',
                        ]);
                    } else {
                        $pd->update([
                            'qty' => $filtered['qty'][$i] + $pd->qty,
                            'description' => $filtered['description'][$i] ?? $pd->description,
                        ]);

                        $stockTransaction = StockTransaction::where('itemCode', $filtered['itemCode'][$i])->where('transactionDetailCode', $pd->code)->first();

                        if ($stockTransaction) {
                            $stockTransaction->update([
                                'qtyIn' => $filtered['qty'][$i] + $stockTransaction->qtyIn,
                            ]);
                        }
                    }
                }

                // $this->logActivity('Purchase Detail', $purchaseDetail, 'Create');
            }
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
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

        $this->service->where('id', $id)->delete();
    }
}
