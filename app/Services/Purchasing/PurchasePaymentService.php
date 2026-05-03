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

    public function update($request, $id, $title, $totalPrice)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $purchase = $this->getById($id);

        $paymentAmount = (float) str_replace(['Rp', '.', ','], '', $request->nominal);
        $newPaidAmount = $purchase->paidAmount + $paymentAmount;
        $remainingAmount = $totalPrice - $newPaidAmount;

        $status = $purchase->status;
        $paymentStatus = 'Partial';

        if ($remainingAmount <= 0) {
            $status = 3;
            $paymentStatus = 'Paid';
        }

        // Create Payment History
        \App\Models\Purchasing\PurchasePaymentHistory::create([
            'purchaseCode' => $purchase->code,
            'amount' => $paymentAmount,
            'paymentDate' => $request->paymentDate,
            'userBankCode' => $request->userBankCode,
            'description' => $request->description,
        ]);

        $this->service->where('id', $id)->update([
            'status' => $status,
            'paymentDate' => $request->paymentDate, // Update the last payment date
            'paymentCode' => $purchase->paymentCode ?? GenerateCode::generateCode('FPY'),
            'paidAmount' => $newPaidAmount,
            'paymentStatus' => $paymentStatus,
            'userBankCode' => $request->userBankCode,
        ]);

        // LiveMutationHelper::updateLiveMutation($request->userBankCode, $paymentAmount, 'credit');

        // $this->mutation->create([
        //     'code' => GenerateCode::generateCode('FMT'),
        //     'userBankCode' => $request->userBankCode,
        //     'nominal' => $paymentAmount,
        //     'type' => 'Out',
        //     'date' => Carbon::now(),
        //     'description' => 'Purchase Payment with amount ' . number_format($paymentAmount, 0, '.', ','),
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
