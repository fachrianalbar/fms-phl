<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoicePayment;
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

    public function __construct(InvoicePayment $invoicePayment, Invoice $invoice, LiveMutation $liveMutation, Mutation $mutation)
    {
        $this->service = $invoicePayment;
        $this->invoice = $invoice;
        $this->liveMutation = $liveMutation;
        $this->mutation = $mutation;
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

        $this->service->create([
            'code' => GenerateCode::generateCode('INVP'),
            'invoiceCode' => $data->code,
            'userBankCode' => $request->userBankCode,
            'amount' => $request->amount,
            'invoiceDate' => $request->invoiceDate,
            'description' => $request->description,
            'paymentReceipt' => $paymentReceipt,
        ]);

        LiveMutationHelper::updateLiveMutation($request->userBankCode, (int) $request->amount, 'debit');

        $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'nominal' => $request->amount,
            'type' => 'In',
            'date' => Carbon::now(),
            'description' => 'Invoice Payment with amount '.number_format((int) $request->amount, 0, '.', ','),
            'transactionTypeCode' => 'FTT250306114138',
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function datatable()
    {
        return $this->invoice->with(['details'])->get();
    }
}
