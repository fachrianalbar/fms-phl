<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;

class PurchaseVerificationService
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
        ])->orderBy('date', 'desc')->where('status', 0)->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->where('status', 0)->orderBy('time', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details', 'details.item'])->first();
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $data = $this->getById($id);

        $this->service->where('id', $id)->update([
            'status' => 1,
            'dueDate' => $request->dueDate,
        ]);

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price']);

            for ($i = 0; $i < count($request->itemCode); $i++) {
                $pd = null;

                if (isset($filtered['purchaseDetailCode'][$i])) {
                    $pd = PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->first();

                    if ($pd->itemCode != $filtered['itemCode'][$i]) {
                        $pd->update([
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                        ]);
                    } else {
                        $pd->update([
                            'qty' => $filtered['qty'][$i],
                        ]);
                    }
                    // reload
                    $pd = PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->first();
                } else {
                    $price = (int) str_replace('.', '', $filtered['price'][$i]);

                    Item::where('code', $filtered['itemCode'][$i])->update([
                        'price' => $price,
                    ]);

                    $pd = PurchaseDetail::where('itemCode', $filtered['itemCode'][$i])
                        ->where('purchaseCode', $data->code)
                        ->first();

                    if (! $pd) {
                        $pd = PurchaseDetail::create([
                            'code' => GenerateCode::generateCode('TPD', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'purchaseCode' => $data->code,
                        ]);
                    } else {
                        $pd->update([
                            'qty' => $filtered['qty'][$i] + $pd->qty,
                        ]);
                    }
                }

                if ($pd) {
                    $newQty = (float) $pd->qty;

                    // Cari StockTransaction yang ada
                    $stockTransaction = StockTransaction::query()
                        ->where('transactionCode', $data->code)
                        ->where('transactionDetailCode', $pd->code)
                        ->first();

                    $oldQty = 0;
                    if ($stockTransaction) {
                        $oldQty = (float) $stockTransaction->qtyIn;
                        $stockTransaction->update([
                            'qtyIn' => $newQty,
                            'date' => $request->verifDate ?? ($data->date . ' ' . $data->time),
                            'warehouseCode' => $data->warehouseCode,
                            'transactionType' => 'IN',
                        ]);
                    } else {
                        StockTransaction::create([
                            'code' => GenerateCode::generateCode('FST', true),
                            'itemCode' => $pd->itemCode,
                            'warehouseCode' => $data->warehouseCode,
                            'transactionCode' => $data->code,
                            'transactionDetailCode' => $pd->code,
                            'qtyIn' => $newQty,
                            'qtyOut' => 0,
                            'date' => $request->verifDate ?? ($data->date . ' ' . $data->time),
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
            }

            // Hapus PurchaseDetail dan StockTransaction yang tidak ada di request
            $sentDetailCodes = array_filter($filtered['purchaseDetailCode'] ?? []);
            $deletedDetails = PurchaseDetail::where('purchaseCode', $data->code)
                ->whereNotIn('code', $sentDetailCodes)
                ->get();

            foreach ($deletedDetails as $deleted) {
                // Rollback Stock
                $st = StockTransaction::where('transactionCode', $data->code)
                    ->where('transactionDetailCode', $deleted->code)
                    ->first();
                if ($st) {
                    Stock::where('itemCode', $st->itemCode)->decrement('stockIn', $st->qtyIn);
                    $st->delete();
                }
                $deleted->delete();
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
