<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Traits\LogActivity;
use Carbon\Carbon;
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
            'purchaseStatus'
        ])->orderBy('date', 'desc')->where('status', 0)->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus'
        ])->orderBy('date', 'desc')->where('status', 0)->orderBy('time', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details', 'details.item'])->first();
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'status' => 1,
            'dueDate' => $request->dueDate
        ]);

        if (isset($request->itemCode)) {
            $filtered = Arr::only($request->all(), ['qty', 'itemCode', 'purchaseDetailCode', 'price']);

            for ($i = 0; $i < count($request->itemCode); $i++) {

                if (isset($filtered['purchaseDetailCode'][$i])) {
                    $pd = PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->first();

                    // $price = (int) str_replace('.', '', $filtered['price'][$i]);

                    // Item::where('code', $filtered['itemCode'][$i])->update([
                    //     'price' => $price,
                    // ]);

                    if ($pd->item->code !=  $filtered['itemCode'][$i]) {

                        PurchaseDetail::where('code', $filtered['purchaseDetailCode'][$i])->update([
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'purchaseCode' => $request->code
                        ]);
                    } else {
                        $pd->update([
                            'qty' => $filtered['qty'][$i]
                        ]);
                    }
                } else {
                    $price = (int) str_replace('.', '', $filtered['price'][$i]);


                    Item::where('code', $filtered['itemCode'][$i])->update([
                        'price' => $price,
                    ]);

                    $data = $this->getById($id);
                    $code = $data->code;

                    $pd = PurchaseDetail::where('itemCode', $filtered['itemCode'][$i])
                        ->whereHas('purchase', function ($query) use ($code) {
                            $query->where('code', $code);
                        })
                        ->first();


                    if (!$pd) {
                        PurchaseDetail::create([
                            'code' => GenerateCode::generateCode('TPD', true),
                            'itemCode' => $filtered['itemCode'][$i],
                            'qty' => $filtered['qty'][$i],
                            'purchaseCode' => $request->code
                        ]);
                    } else {
                        $pd->update([
                            'qty' => $filtered['qty'][$i] + $pd->qty
                        ]);
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

        foreach ($data->details as $item) {
            Stock::where('itemCode', $item->item->code)->decrement('stockIn', $item->qty);
            StockTransaction::where('transactionCode', $item->code)->delete();
        }

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
