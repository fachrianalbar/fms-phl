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
            $receivedQty = $request->receivedQty;
        }

        $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price']);

        for ($i = 0; $i < count($selectedPurchase); $i++) {
            $pd = PurchaseDetail::where('id', $selectedPurchase[$i])->first();

            $pd->update([
                'status' => 1,
                'description' => $request->description,
                'receivedQty' => $receivedQty ? (int) $receivedQty : $filtered['qty'][$i],
                'qtyUsed' => 0,
            ]);

            $pd = PurchaseDetail::where('id', $selectedPurchase[$i])->first();

            $stock = Stock::where('itemCode', $pd->item->code)->first();

            if (isset($stock)) {
                Stock::where('itemCode', $pd->item->code)->update([
                    'stockIn' => $stock->stockIn + $pd->receivedQty,
                ]);
            }

            if (! $stock) {
                Stock::create([
                    'code' => GenerateCode::generateCode('FSTC', true),
                    'itemCode' => $pd->itemCode,
                    'stockIn' => $receivedQty ? (int) $receivedQty : $filtered['qty'][$i],
                    'stockOut' => 0,
                ]);
            }

            StockTransaction::create([
                'code' => GenerateCode::generateCode('FPD', true),
                'itemCode' => $pd->itemCode,
                'qty' => $receivedQty ? (int) $receivedQty : $filtered['qty'][$i],
                'transactionCode' => $pd->code,
                'date' => Carbon::now(),
                'type' => 'IN',
                'transactionType' => 1,
            ]);
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            Stock::where('itemCode', $item->item->code)->decrement('stockIn', $item->qty);
            StockTransaction::where('transactionCode', $item->code)->delete();
        }

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
