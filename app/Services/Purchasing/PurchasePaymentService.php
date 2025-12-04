<?php

namespace App\Services\Purchasing;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Inventory\Stock;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Models\Purchasing\Purchase;
use App\Models\StockTransaction;
use App\Traits\LogActivity;
use Carbon\Carbon;

class PurchasePaymentService
{
    use LogActivity;

    protected $service;

    protected $liveMutation;

    protected $mutation;

    public function __construct(Purchase $purchase, LiveMutation $liveMutation, Mutation $mutation)
    {
        $this->service = $purchase;
        $this->liveMutation = $liveMutation;
        $this->mutation = $mutation;
    }

    public function findAll()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->whereIn('status', [2, 3])->orderBy('time', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'supplier',
            'warehouse',
            'purchaseStatus',
        ])->orderBy('date', 'desc')->whereIn('status', [2, 3])->orderBy('time', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details',
            'details.item',
            'purchaseStatus',
        ])->first();
    }

    public function update($request, $id, $title, $totalPrice)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'status' => 3,
            'paymentDate' => $request->paymentDate,
            'paymentCode' => GenerateCode::generateCode('FPY'),
            'nominal' => $totalPrice,
            'userBankCode' => $request->userBankCode,
        ]);

        // LiveMutationHelper::updateLiveMutation($request->userBankCode, $totalPrice, 'credit');

        // $this->mutation->create([
        //     'code' => GenerateCode::generateCode('FMT'),
        //     'userBankCode' => $request->userBankCode,
        //     'nominal' => $totalPrice,
        //     'type' => 'Out',
        //     'date' => Carbon::now(),
        //     'description' => 'Purchase Payment with amount ' . number_format($totalPrice, 0, '.', ','),
        //     'transactionTypeCode' => 'FTT250306114138'
        // ]);

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
        StockTransaction::withTrashed()->where('transactionCode', $data->code)->delete();

        $data->details()->delete();

        $this->service->where('id', $id)->delete();
    }
}
