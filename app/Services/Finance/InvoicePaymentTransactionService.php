<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoicePayment;
use App\Models\Finance\InvoicePaymentClaim;
use App\Models\Finance\InvoicePaymentTransaction;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoicePaymentTransactionService
{
    use LogActivity;

    protected $service;

    protected $invoice;

    protected $payment;

    protected $claim;

    protected $mutation;

    public function __construct(InvoicePaymentTransaction $transaction, Invoice $invoice, InvoicePayment $payment, InvoicePaymentClaim $claim, Mutation $mutation)
    {
        $this->service = $transaction;
        $this->invoice = $invoice;
        $this->payment = $payment;
        $this->claim = $claim;
        $this->mutation = $mutation;
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)
            ->with([
                'customer.pic',
                'userBank.bank',
                'payments.invoice.customer',
                'claims.invoice',
            ])
            ->first();
    }

    public function datatable()
    {
        return $this->service->with([
            'customer',
            'userBank.bank',
            'payments.invoice',
            'claims.invoice',
        ])
            ->orderBy('paymentDate', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Daftar invoice belum lunas milik satu customer (untuk form transaksi).
     */
    public function getOpenInvoicesByCustomer(string $customerCode)
    {
        return $this->invoice->with(['payments', 'claims'])
            ->where('customerCode', $customerCode)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', Invoice::STATUS_FULL);
            })
            ->orderBy('invoiceDate', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Simpan transaksi pembayaran (1 transaksi untuk banyak invoice + claim).
     *
     * Alur:
     * 1. Lock invoice yang terlibat
     * 2. Buat header transaksi
     * 3. Buat alokasi pembayaran per invoice (invoice_payment)
     * 4. Buat claim per invoice (invoice_payment_claim)
     * 5. Catat kas masuk SEKALI (LiveMutation + Mutation) sebesar uang riil
     * 6. Update status tiap invoice (dibayar + claim >= tagihan => Lunas)
     */
    public function store(Request $request): InvoicePaymentTransaction
    {
        DB::beginTransaction();

        try {
            $items = collect((array) $request->input('invoices', []))
                ->filter(fn ($item) => ! empty($item['code'] ?? null))
                ->values();

            if ($items->isEmpty()) {
                throw new \InvalidArgumentException('Pilih minimal satu faktur untuk dibayar.');
            }

            // Validasi invoice milik customer yang sama + lock untuk mencegah race condition
            $invoiceCodes = $items->pluck('code')->all();
            $invoices = $this->invoice->newQuery()
                ->whereIn('code', $invoiceCodes)
                ->where('customerCode', $request->customerCode)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get()
                ->keyBy('code');

            foreach ($items as $item) {
                if (! $invoices->has($item['code'])) {
                    throw new \InvalidArgumentException('Faktur tidak ditemukan atau bukan milik customer yang dipilih: '.$item['code']);
                }
            }

            // Upload bukti pembayaran
            $paymentReceipt = null;
            if ($request->hasFile('paymentReceipt')) {
                $file = $request->file('paymentReceipt');
                $paymentReceipt = str_replace(' ', '_', time().'_'.$file->getClientOriginalName());
                Storage::putFileAs('public/invoice-payment', $file, $paymentReceipt);
            }

            $paymentDate = $request->paymentDate ?: Carbon::now()->toDateString();

            // 1. Header transaksi
            $transaction = $this->service->create([
                'code' => GenerateCode::generateUniqueCode('INVT', 'invoice_payment_transaction'),
                'paymentDate' => $paymentDate,
                'customerCode' => $request->customerCode,
                'userBankCode' => $request->userBankCode,
                'amount' => 0,
                'totalClaim' => 0,
                'description' => $request->description,
                'paymentReceipt' => $paymentReceipt,
            ]);

            $totalAmount = 0;
            $totalClaim = 0;
            $paidInvoiceNumbers = [];

            foreach ($items as $item) {
                /** @var Invoice $invoice */
                $invoice = $invoices->get($item['code']);

                $amount = (int) round((float) ($item['amount'] ?? 0));
                $claimAmount = (int) round((float) ($item['claim'] ?? 0));

                if ($amount < 0 || $claimAmount < 0) {
                    throw new \InvalidArgumentException('Nominal bayar / claim tidak boleh negatif.');
                }

                // Hitung sisa tagihan terkini (locking read agar aman dari transaksi paralel)
                $billing = $this->getInvoiceBilling($invoice);
                $paidSoFar = (float) $this->payment->newQuery()
                    ->where('invoiceCode', $invoice->code)
                    ->lockForUpdate()
                    ->sum('amount');
                $claimSoFar = (float) $this->claim->newQuery()
                    ->where('invoiceCode', $invoice->code)
                    ->lockForUpdate()
                    ->sum('amount');
                $remaining = $billing - $paidSoFar - $claimSoFar;

                if ($amount + $claimAmount > $remaining + 0.01) {
                    throw new \InvalidArgumentException(
                        'Total bayar + claim untuk faktur '.($invoice->invoiceNumber ?: $invoice->code)
                        .' (Rp '.number_format($amount + $claimAmount, 0, ',', '.').') '
                        .'melebihi sisa tagihan (Rp '.number_format(max($remaining, 0), 0, ',', '.').').'
                    );
                }

                // 2. Alokasi pembayaran per invoice (hanya jika ada uang masuk)
                if ($amount > 0) {
                    $this->payment->create([
                        'code' => GenerateCode::generateUniqueCode('INVP', 'invoice_payment'),
                        'transactionCode' => $transaction->code,
                        'invoiceCode' => $invoice->code,
                        'userBankCode' => $request->userBankCode,
                        'paymentDate' => $paymentDate,
                        'amount' => $amount,
                        'ppnAmount' => $invoice->ppnAmount ?? 0,
                        'pphAmount' => $invoice->pphAmount ?? 0,
                        'description' => $request->description,
                        'paymentReceipt' => $paymentReceipt,
                    ]);

                    $totalAmount += $amount;
                    $paidInvoiceNumbers[] = $invoice->invoiceNumber ?: $invoice->code;
                }

                // 3. Claim pengurang tagihan
                if ($claimAmount > 0) {
                    $this->claim->create([
                        'code' => GenerateCode::generateUniqueCode('CLM', 'invoice_payment_claim'),
                        'transactionCode' => $transaction->code,
                        'invoiceCode' => $invoice->code,
                        'description' => $item['claimDescription'] ?? null,
                        'amount' => $claimAmount,
                    ]);

                    $totalClaim += $claimAmount;
                }
            }

            if ($totalAmount <= 0 && $totalClaim <= 0) {
                throw new \InvalidArgumentException('Minimal satu faktur harus diisi nominal bayar atau claim.');
            }

            $transaction->amount = $totalAmount;
            $transaction->totalClaim = $totalClaim;
            $transaction->save();

            // 4. Catat kas masuk SEKALI per transaksi (bukan per invoice)
            if ($totalAmount > 0) {
                LiveMutationHelper::updateLiveMutation($request->userBankCode, $totalAmount, 'debit');

                $mutationDescription = 'Pembayaran Invoice ('.count($paidInvoiceNumbers).' faktur): '.implode(', ', $paidInvoiceNumbers)
                    .' dengan jumlah '.number_format($totalAmount, 0, '.', ',');

                $this->mutation->create([
                    'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
                    'userBankCode' => $request->userBankCode,
                    'nominal' => $totalAmount,
                    'type' => 'In',
                    'date' => Carbon::now(),
                    'description' => $mutationDescription,
                    'transactionTypeCode' => 'FTT250306113138',
                ]);
            }

            // 5. Update status tiap invoice
            foreach ($invoices as $invoice) {
                $this->recalculateInvoiceStatus($invoice->code);
            }

            $this->logActivity('Payment Transaction', $transaction, 'Create');

            DB::commit();

            return $transaction;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Hitung total tagihan invoice (DPP + PPN - PPH).
     */
    public function getInvoiceBilling(Invoice $invoice): float
    {
        return (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
    }

    /**
     * Hitung ulang status pembayaran satu invoice:
     * (total dibayar + total claim) >= total tagihan => STATUS_FULL.
     *
     * Dipakai bersama oleh transaksi pembayaran baru maupun jalur lama.
     */
    public function recalculateInvoiceStatus(string $invoiceCode): int
    {
        $invoice = $this->invoice->newQuery()->where('code', $invoiceCode)->first();

        if (! $invoice) {
            return Invoice::STATUS_CREATE;
        }

        $billing = $this->getInvoiceBilling($invoice);
        $totalPaid = (float) $this->payment->newQuery()
            ->where('invoiceCode', $invoiceCode)
            ->whereNull('deleted_at')
            ->sum('amount');
        $totalClaim = (float) $this->claim->newQuery()
            ->where('invoiceCode', $invoiceCode)
            ->whereNull('deleted_at')
            ->sum('amount');
        $settled = $totalPaid + $totalClaim;

        $status = Invoice::STATUS_CREATE;
        if ($billing > 0 && $settled >= $billing) {
            $status = Invoice::STATUS_FULL;
        } elseif ($settled > 0) {
            $status = Invoice::STATUS_PARTIAL;
        }

        $this->invoice->newQuery()->where('id', $invoice->id)->update(['status' => $status]);

        return $status;
    }

    /**
     * Bungkus pembayaran tunggal (jalur lama) ke dalam transaksi pembayaran
     * agar seluruh riwayat tercatat di daftar Transaksi Pembayaran.
     */
    public function wrapSinglePayment(
        Invoice $invoice,
        string $userBankCode,
        $amount,
        $paymentDate,
        $description = null,
        $paymentReceipt = null
    ): InvoicePaymentTransaction {
        return $this->service->create([
            'code' => GenerateCode::generateUniqueCode('INVT', 'invoice_payment_transaction'),
            'paymentDate' => $paymentDate ?: Carbon::now()->toDateString(),
            'customerCode' => $invoice->customerCode,
            'userBankCode' => $userBankCode,
            'amount' => (int) $amount,
            'totalClaim' => 0,
            'description' => $description,
            'paymentReceipt' => $paymentReceipt,
        ]);
    }
}
