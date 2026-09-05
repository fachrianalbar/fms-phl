<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoicePayment;
use App\Models\Finance\InvoicePaymentClaim;
use App\Models\Finance\InvoicePaymentTransaction;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InvoicePaymentService
{
    use LogActivity;

    protected $service;

    protected $invoice;

    protected $liveMutation;

    protected $mutation;

    protected $transaction;

    protected $claim;

    public function __construct(InvoicePayment $invoicePayment, Invoice $invoice, LiveMutation $liveMutation, Mutation $mutation, InvoicePaymentTransaction $transaction, InvoicePaymentClaim $claim)
    {
        $this->service = $invoicePayment;
        $this->invoice = $invoice;
        $this->liveMutation = $liveMutation;
        $this->mutation = $mutation;
        $this->transaction = $transaction;
        $this->claim = $claim;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details', 'customer'])->first();
    }

    public function update($request, $id, $title)
    {
        $data = $this->invoice->where('id', $id)->first();

        $paymentReceipt = null;
        if ($request->paymentReceipt) {
            $file = $request->paymentReceipt;
            $paymentReceipt = $file->getClientOriginalName();

            $paymentReceipt = str_replace(' ', '_', $paymentReceipt);

            $path = 'public/invoice-payment';

            Storage::putFileAs($path, $file, $paymentReceipt);
        }

        // Buat header transaksi pembayaran agar tercatat di daftar Transaksi Pembayaran
        $transaction = $this->transaction->create([
            'code' => GenerateCode::generateUniqueCode('INVT', 'invoice_payment_transaction'),
            'paymentDate' => $request->paymentDate ?: Carbon::now()->toDateString(),
            'customerCode' => $data->customerCode,
            'userBankCode' => $request->userBankCode,
            'amount' => (int) $request->amount,
            'totalClaim' => 0,
            'description' => $request->description,
            'paymentReceipt' => $paymentReceipt,
        ]);

        $this->service->create([
            'code' => GenerateCode::generateUniqueCode('INVP', 'invoice_payment'),
            'transactionCode' => $transaction->code,
            'invoiceCode' => $data->code,
            'userBankCode' => $request->userBankCode,
            'amount' => $request->amount,
            'paymentDate' => $request->paymentDate ?: Carbon::now()->toDateString(),
            'description' => $request->description,
            'paymentReceipt' => $paymentReceipt,
        ]);

        LiveMutationHelper::updateLiveMutation($request->userBankCode, (int) $request->amount, 'debit');

        $this->mutation->create([
            'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
            'userBankCode' => $request->userBankCode,
            'nominal' => $request->amount,
            'type' => 'In',
            'date' => Carbon::now(),
            'description' => 'Invoice Payment with amount '.number_format((int) $request->amount, 0, '.', ','),
            'transactionTypeCode' => 'FTT250306113138',
        ]);

        $this->logActivity($title, $data, 'Create');

        // Update invoice status based on payments (1: created, 2: partial, 3: full)
        try {
            $sumPayments = (int) $this->service->where('invoiceCode', $data->code)->sum('amount');
            $sumClaims = (int) $this->claim->where('invoiceCode', $data->code)->whereNull('deleted_at')->sum('amount');
            $settled = $sumPayments + $sumClaims;
            $invoiceTotal = (int) (($data->invoiceAmount ?? 0) + ($data->ppnAmount ?? 0) - ($data->pphAmount ?? 0));

            $nextStatus = Invoice::STATUS_CREATE;
            if ($invoiceTotal > 0 && $settled >= $invoiceTotal) {
                $nextStatus = Invoice::STATUS_FULL;
            } elseif ($settled > 0) {
                $nextStatus = Invoice::STATUS_PARTIAL;
            }

            $this->invoice->where('id', $data->id)->update(['status' => $nextStatus]);
        } catch (\Exception $e) {
            // if updating status fails do not block payment creation, but log error
            logger()->error('Failed to update invoice status for invoice '.$data->code.': '.$e->getMessage());
        }
    }

    public function datatable()
    {
        return $this->invoice->with(['details', 'payments.userBank.bank', 'claims', 'customer'])
            ->whereHas('payments')
            ->get();
    }
}
