<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\UserBank;
use App\Models\Finance\VendorPayment;
use App\Models\Finance\VendorPaymentHistory;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;

class VendorPaymentService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $userBank;

    protected $mutation;

    protected $paymentHistory;

    public function __construct(VendorPayment $vendorPayment, Order $order, UserBank $userBank, Mutation $mutation, VendorPaymentHistory $paymentHistory)
    {
        $this->service = $vendorPayment;
        $this->order = $order;
        $this->userBank = $userBank;
        $this->mutation = $mutation;
        $this->paymentHistory = $paymentHistory;
    }

    public function findAll()
    {
        return $this->order->whereHas('fleet.company', function ($q) {
            $q->whereRaw('LOWER(type) = ?', ['external']);
        })
            ->with(['fleet', 'fleet.company', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'vendorPayments'])
            ->orderBy('orderDate', 'asc')
            ->get();
    }

    public function store($request, $title)
    {
        $orderCodes = collect($request->orderCodes ?? [])->filter()->unique()->values();
        $userBank = $this->userBank->where('code', $request->userBankCode)->first();

        if ($orderCodes->isEmpty()) {
            throw new \Exception('Order yang dipilih tidak valid.');
        }

        if (! $userBank) {
            throw new \Exception('Sumber dana (bank) tidak ditemukan.');
        }

        // Satu kode batch untuk satu kali submit pembayaran multi-order.
        $batchCode = GenerateCode::generateCode('FVB', true);

        $processedOrderCodes = [];
        $skippedOrderCodes = [];

        foreach ($orderCodes as $orderCode) {
            $order = $this->order->where('code', $orderCode)->first();

            if (! $order) {
                throw new \Exception('Order ' . $orderCode . ' tidak ditemukan.');
            }

            // Cek apakah sudah ada vendor payment untuk order ini
            $vendorPayment = $this->service->where('orderCode', $orderCode)->first();

            // Tagihan awal dari order
            $billingAmount = $vendorPayment ? (float) ($vendorPayment->amount ?? 0) : (float) ($order->vendorPrice ?? 0);

            $created = false;
            if (! $vendorPayment) {
                // Pertama kali: buat vendor payment baru
                // amount = tagihan awal, bukan jumlah pembayaran
                $vendorPayment = $this->service->create([
                    'date' => $request->date,
                    'amount' => $billingAmount,  // Tagihan awal
                    'paid_amount' => 0,
                    'remaining_amount' => $billingAmount,
                    'payment_status' => 'pending',
                    'description' => $request->description,
                    'orderCode' => $orderCode,
                    'code' => $batchCode,
                ]);
                $created = true;
            }

            // Pembayaran vendor
            $remainingAmount = (float) ($vendorPayment->remaining_amount ?? 0);
            if ($remainingAmount <= 0) {
                $skippedOrderCodes[] = $orderCode;
                continue;
            }

            if (count($orderCodes) === 1 && $request->filled('paymentAmount')) {
                $paymentAmount = (float) $request->paymentAmount;
                if ($paymentAmount > $remainingAmount) {
                    throw new \Exception('Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp ' . number_format($remainingAmount, 0, ',', '.') . ').');
                }
            } else {
                // Jika batch (lebih dari 1), wajib bayar lunas dari sisa tagihannya
                $paymentAmount = $remainingAmount;
            }

            // Update paid amount dan remaining amount
            $newPaidAmount = ((float) ($vendorPayment->paid_amount ?? 0)) + $paymentAmount;
            $newRemainingAmount = max(0, ((float) ($vendorPayment->amount ?? $billingAmount)) - $newPaidAmount);

            // Karena full payment, status selalu paid saat berhasil diproses
            $paymentStatus = $newRemainingAmount <= 0 ? 'paid' : 'partial';

            // Update vendor payment record
            $vendorPayment->update([
                'date' => $request->date,
                'code' => $batchCode,
                'description' => $request->description,
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'payment_status' => $paymentStatus,
            ]);

            // Simpan payment history
            $this->paymentHistory->create([
                'vendor_payment_id' => $vendorPayment->id,
                'amount' => $paymentAmount,
                'payment_date' => $request->date,
                'user_bank_code' => $request->userBankCode,
                'description' => $request->description,
            ]);

            // Update LiveMutation dengan CREDIT (pengeluaran uang)
            LiveMutationHelper::updateLiveMutation($userBank->code, $paymentAmount, 'credit');

            // Create mutation record for accounting
            $this->mutation->create([
                'code' => GenerateCode::generateCode('FMT', true),
                'userBankCode' => $request->userBankCode,
                'nominal' => $paymentAmount,
                'type' => 'Out', // Out untuk pengeluaran
                'date' => $request->date,
                'description' => 'Vendor Payment Batch ' . $batchCode . ' for Order ' . $order->code . ' with amount ' . number_format($paymentAmount, 0, '.', ','),
                'transactionCode' => $batchCode,
                'transactionTypeCode' => 'FTT251208001130', // Vendor Payment transaction type
            ]);

            // Update order status jika sudah full bayar
            if ($paymentStatus === 'paid') {
                $order->update([
                    'status' => 6, // Status paid
                ]);
            }

            $this->logActivity($title, $vendorPayment, $created ? 'Create' : 'Update');
            $processedOrderCodes[] = $orderCode;
        }

        if (empty($processedOrderCodes)) {
            throw new \Exception('Semua order terpilih sudah lunas.');
        }

        return [
            'batch_code' => $batchCode,
            'processed_count' => count($processedOrderCodes),
            'skipped_count' => count($skippedOrderCodes),
            'processed_order_codes' => $processedOrderCodes,
            'skipped_order_codes' => $skippedOrderCodes,
        ];
    }

    /**
     * Membatalkan pembayaran vendor (hard delete).
     *
     * @param string $orderCode
     * @param string $title
     * @throws \Exception
     */
    public function cancelPayment($orderCode, $title)
    {
        $vendorPayment = $this->service->where('orderCode', $orderCode)->first();

        if (! $vendorPayment) {
            throw new \Exception('Data pembayaran vendor tidak ditemukan untuk order ini.');
        }

        $order = $this->order->where('code', $orderCode)->first();
        if (! $order) {
            throw new \Exception('Data order tidak ditemukan.');
        }

        // 1. Dapatkan riwayat pembayaran untuk mengembalikan saldo bank
        $histories = $vendorPayment->paymentHistory;

        foreach ($histories as $history) {
            $amount = (float) $history->amount;
            $bankCode = $history->user_bank_code;

            // Kembalikan saldo LiveMutation (kurangi credit)
            $liveMutation = \App\Models\LiveMutation::where('userBankCode', $bankCode)->first();
            if ($liveMutation) {
                $liveMutation->credit -= $amount;
                $liveMutation->balance = $liveMutation->debit - $liveMutation->credit;
                $liveMutation->save();
            }

            // Hapus Mutation record (hard delete)
            \App\Models\Mutation::where('transactionCode', $vendorPayment->code)
                ->where('userBankCode', $bankCode)
                ->where('description', 'like', '%' . $orderCode . '%')
                ->forceDelete();

            // Hapus history record (hard delete)
            $history->forceDelete();
        }

        // 2. Hapus VendorPayment record (hard delete)
        $vendorPayment->forceDelete();

        // 3. Kembalikan status Order
        // Jika order sudah di-invoice, statusnya 5 (Order Invoice).
        // Jika tidak, statusnya 4 (Order Return Do).
        $isInvoiced = \App\Models\Finance\InvoiceDetail::where('orderCode', $orderCode)
            ->whereNull('deleted_at')
            ->exists();

        $order->update([
            'status' => $isInvoiced ? 5 : 4,
        ]);

        $this->logActivity($title, $vendorPayment, 'Cancel Payment');
    }

    /**
     * Generate nomor nota berformat PREFIX/SEQUENCE/YEAR.
     * SEQUENCE = urutan yang reset setiap tahun baru.
     */
    public function generateNotaNumber($prefix)
    {
        $year = now()->format('Y');
        $pattern = $prefix . '/%/' . $year;

        // Cari nota_number tertinggi dengan prefix dan tahun ini
        $lastNota = $this->service
            ->where('nota_number', 'like', $pattern)
            ->orderByDesc('nota_number')
            ->value('nota_number');

        if ($lastNota) {
            $parts = explode('/', $lastNota);
            if (count($parts) === 3) {
                $lastSequence = (int) $parts[1];
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }
        } else {
            $nextSequence = 1;
        }

        return $prefix . '/' . str_pad($nextSequence, 5, '0', STR_PAD_LEFT) . '/' . $year;
    }

    /**
     * Assign nomor nota ke beberapa order sekaligus.
     *
     * @param array $orderCodes
     * @param string $userBankCode
     * @param string $title
     * @return string Nomor nota yang dihasilkan
     * @throws \Exception
     */
    public function assignNota(array $orderCodes, $userBankCode, $title)
    {
        $orderCodes = array_values(array_unique(array_filter($orderCodes)));

        if (empty($orderCodes)) {
            throw new \Exception('Pilih minimal satu order untuk di-nota-kan.');
        }

        // Ambil semua order terpilih dengan relasi customer, company, dan fleet
        $orders = $this->order->with(['customer.company', 'fleet.company'])->whereIn('code', $orderCodes)->get();
        if ($orders->count() !== count($orderCodes)) {
            throw new \Exception('Beberapa order tidak ditemukan.');
        }

        // Validasi 1: Perusahaan kendaraan (fleet company) yang berbeda tidak boleh dalam satu nota
        $fleetCompanyCodes = $orders->map(function ($order) {
            return $order->fleet->fleetCompanyCode ?? null;
        })->filter()->unique();
        if ($fleetCompanyCodes->count() > 1) {
            throw new \Exception('Gagal: Order yang dipilih memiliki perusahaan kendaraan yang berbeda. Satu nota hanya diperbolehkan untuk perusahaan kendaraan yang sama.');
        }

        // Validasi 3: Format Perusahaan (Pribadi, PHL, WTMS) yang berbeda tidak boleh dalam satu nota
        $companyFormats = $orders->map(function ($order) {
            return strtoupper(trim((string) ($order->customer->company->format ?? '')));
        })->filter()->unique();
        if ($companyFormats->count() > 1) {
            throw new \Exception('Gagal: Order yang dipilih memiliki format perusahaan yang berbeda (' . $companyFormats->implode(', ') . '). Semua order dalam satu nota harus memiliki format perusahaan yang sama.');
        }

        // Cari vendor payment yang sudah ada untuk order-order ini
        $vendorPayments = $this->service
            ->whereIn('orderCode', $orderCodes)
            ->get();

        // Validasi: tidak boleh ada order yang sudah memiliki nota
        $alreadyNota = $vendorPayments->whereNotNull('nota_number');
        if ($alreadyNota->isNotEmpty()) {
            throw new \Exception('Order sudah memiliki nota: ' . $alreadyNota->pluck('orderCode')->implode(', '));
        }

        // Ambil format perusahaan dari order pertama
        $firstOrder = $orders->first();
        $companyFormat = strtoupper(trim((string) ($firstOrder->customer->company->format ?? '')));

        // Map format ke prefix nota
        if ($companyFormat === 'P') {
            $prefix = 'P';
        } elseif ($companyFormat === 'WTMS' || $companyFormat === 'WT') {
            $prefix = 'WTMS';
        } else {
            $prefix = 'PHL';
        }

        $notaNumber = $this->generateNotaNumber($prefix);

        $logPayment = null;

        foreach ($orderCodes as $orderCode) {
            $order = $orders->firstWhere('code', $orderCode);
            $vendorPayment = $vendorPayments->firstWhere('orderCode', $orderCode);

            if ($vendorPayment) {
                // Update yang sudah ada
                $vendorPayment->update([
                    'nota_number' => $notaNumber,
                    'user_bank_code' => $userBankCode,
                ]);
            } else {
                // Buat baru jika belum ada
                $vendorPayment = $this->service->create([
                    'date' => now()->format('Y-m-d'),
                    'amount' => $order->vendorPrice ?? 0,
                    'paid_amount' => 0,
                    'remaining_amount' => $order->vendorPrice ?? 0,
                    'payment_status' => 'pending',
                    'orderCode' => $orderCode,
                    'nota_number' => $notaNumber,
                    'user_bank_code' => $userBankCode,
                ]);
            }

            if (!$logPayment) {
                $logPayment = $vendorPayment;
            }
        }

        // Log activity
        if ($logPayment) {
            $this->logActivity($title, $logPayment, 'Generate Nota ' . $notaNumber);
        }

        return $notaNumber;
    }

    public function cancelNota($orderCode, $title)
    {
        $vendorPayment = $this->service->where('orderCode', $orderCode)->first();

        if (!$vendorPayment) {
            throw new \Exception('Data pembayaran tidak ditemukan.');
        }

        $notaNumber = $vendorPayment->nota_number;

        if ($notaNumber) {
            // Ambil semua vendor payment yang memiliki nomor nota yang sama
            $paymentsInNota = $this->service->where('nota_number', $notaNumber)->get();

            // Validasi: tidak boleh ada yang sudah dibayar di grup nota ini
            $alreadyPaid = $paymentsInNota->filter(function ($vp) {
                return $vp->paid_amount > 0 || $vp->payment_status !== 'pending';
            });

            if ($alreadyPaid->isNotEmpty()) {
                throw new \Exception('Nota tidak dapat dibatalkan karena beberapa order di dalam nota ini (' . $notaNumber . ') sudah dibayar. Batalkan pembayaran terlebih dahulu.');
            }

            // Hapus semua record vendor_payment di grup nota ini secara fisik (hard delete)
            foreach ($paymentsInNota as $payment) {
                $payment->forceDelete();
            }

            // Log activity
            $this->logActivity($title, $vendorPayment, 'Cancel Nota ' . $notaNumber . ' (All associated orders reset)');
        } else {
            if ($vendorPayment->paid_amount > 0 || $vendorPayment->payment_status !== 'pending') {
                throw new \Exception('Pembayaran sudah dilakukan, tidak dapat dibatalkan.');
            }
            $vendorPayment->forceDelete();
            $this->logActivity($title, $vendorPayment, 'Cancel Unassigned Payment Record');
        }
    }
}

