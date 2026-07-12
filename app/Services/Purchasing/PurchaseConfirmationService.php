<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PurchaseConfirmationService
{
    use LogActivity;

    protected $service;

    public function __construct(Purchase $purchase)
    {
        $this->service = $purchase;
    }

    public function findAll()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->whereIn('status', [1, 2])->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->whereIn('status', [1, 2])->orderBy('time', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details',
            'details.item',
            'purchaseStatus',
        ])->first();
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'status' => 2,
            'receivedDate' => $request->receivedDate,
        ]);

        $selectedPurchase = $request->input('confirm');
        $receivedQty = null;

        if (count($selectedPurchase) == 1) {
            $receivedQty = (float) $request->receivedQty;
        }

        $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price']);

        for ($i = 0; $i < count($selectedPurchase); $i++) {
            $pd = PurchaseDetail::where('id', $selectedPurchase[$i])->with('purchase')->first();

            $pd->update([
                'status' => 1,
                'description' => $request->description,
                'receivedQty' => $receivedQty ?: $filtered['qty'][$i],
                'qtyUsed' => 0,
            ]);

            $newQty = $receivedQty ?: $filtered['qty'][$i];

            // Cari StockTransaction yang ada
            $stockTransaction = StockTransaction::query()
                ->where('transactionCode', $pd->purchaseCode)
                ->where('transactionDetailCode', $pd->code)
                ->first();

            $oldQty = 0;
            if ($stockTransaction) {
                $oldQty = (float) $stockTransaction->qtyIn;
                $stockTransaction->update([
                    'qtyIn' => $newQty,
                    'date' => $request->receivedDate ?? Carbon::now(),
                    'warehouseCode' => $pd->purchase->warehouseCode ?? null,
                    'transactionType' => 'IN',
                ]);
            } else {
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FST', true),
                    'itemCode' => $pd->itemCode,
                    'warehouseCode' => $pd->purchase->warehouseCode ?? null,
                    'transactionCode' => $pd->purchaseCode,
                    'transactionDetailCode' => $pd->code,
                    'qtyIn' => $newQty,
                    'qtyOut' => 0,
                    'date' => $request->receivedDate ?? Carbon::now(),
                    'transactionType' => 'IN',
                ]);
            }

            // Update tabel Stock berdasarkan selisih (newQty - oldQty)
            $diffQty = $newQty - $oldQty;
            if (abs($diffQty) > 0.0001) {
                $stock = Stock::where('itemCode', $pd->itemCode)->first();
                if ($stock) {
                    $stock->update([
                        'stockIn' => $stock->stockIn + $diffQty,
                    ]);
                } else {
                    Stock::create([
                        'code' => GenerateCode::generateCode('FSTC', true),
                        'itemCode' => $pd->itemCode,
                        'stockIn' => $newQty,
                        'stockOut' => 0,
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

        // Rollback stock: kurangi stockIn berdasarkan total qtyIn dari StockTransaction
        $stockTransactions = StockTransaction::where('transactionCode', $data->code)->get();

        foreach ($stockTransactions as $transaction) {
            Stock::where('itemCode', $transaction->itemCode)->decrement('stockIn', $transaction->qtyIn);
        }

        // Delete semua StockTransaction untuk purchase ini
        StockTransaction::where('transactionCode', $data->code)->delete();

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
