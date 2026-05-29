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
 
        // 1. Hitung sisa tagihan saat ini untuk masing-masing order
        $orderRemainingAmounts = [];
        $totalRemaining = 0;
        foreach ($orderCodes as $orderCode) {
            $vp = $this->service->where('orderCode', $orderCode)->first();
            $order = $this->order->where('code', $orderCode)->first();
            if (!$order) {
                throw new \Exception('Order ' . $orderCode . ' tidak ditemukan.');
            }
            $rem = $vp ? (float)$vp->remaining_amount : (float)($order->vendorPrice ?? 0);
            $orderRemainingAmounts[$orderCode] = $rem;
            $totalRemaining += $rem;
        }

        $totalPaymentAmount = $request->filled('paymentAmount') ? (float) $request->paymentAmount : null;
        if ($totalPaymentAmount !== null && $totalPaymentAmount > $totalRemaining) {
            throw new \Exception('Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp ' . number_format($totalRemaining, 0, ',', '.') . ').');
        }

        // 2. Alokasikan pembayaran secara merata di antara order yang belum lunas
        $allocations = [];
        foreach ($orderCodes as $orderCode) {
            $allocations[$orderCode] = 0;
        }

        if ($totalPaymentAmount !== null) {
            $remainingToAllocate = $totalPaymentAmount;
            while ($remainingToAllocate > 0.01) {
                $eligibleOrders = [];
                foreach ($orderCodes as $orderCode) {
                    $rem = $orderRemainingAmounts[$orderCode] - $allocations[$orderCode];
                    if ($rem > 0) {
                        $eligibleOrders[] = $orderCode;
                    }
                }
                
                if (empty($eligibleOrders)) {
                    break;
                }
                
                $share = $remainingToAllocate / count($eligibleOrders);
                $allocatedInThisRound = 0;
                
                foreach ($eligibleOrders as $orderCode) {
                    $rem = $orderRemainingAmounts[$orderCode] - $allocations[$orderCode];
                    $amountToAlloc = min($share, $rem);
                    $allocations[$orderCode] += $amountToAlloc;
                    $allocatedInThisRound += $amountToAlloc;
                }
                
                $remainingToAllocate -= $allocatedInThisRound;
                if ($allocatedInThisRound <= 0) {
                    break;
                }
            }
        } else {
            // Full payment for each order
            foreach ($orderCodes as $orderCode) {
                $allocations[$orderCode] = $orderRemainingAmounts[$orderCode];
            }
        }

        foreach ($orderCodes as $orderCode) {
            $order = $this->order->where('code', $orderCode)->first();
            $paymentAmount = $allocations[$orderCode];

            if ($paymentAmount <= 0) {
                $skippedOrderCodes[] = $orderCode;
                continue;
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
 
            // Update paid amount dan remaining amount
            $newPaidAmount = ((float) ($vendorPayment->paid_amount ?? 0)) + $paymentAmount;
            $newRemainingAmount = max(0, ((float) ($vendorPayment->amount ?? $billingAmount)) - $newPaidAmount);
 
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

        // Get all vendor payments sharing the same payment batch code (code column)
        // If code is empty, default to only this vendor payment
        $batchCode = $vendorPayment->code;
        if ($batchCode) {
            $paymentsInBatch = $this->service->where('code', $batchCode)->get();
        } else {
            $paymentsInBatch = collect([$vendorPayment]);
        }

        foreach ($paymentsInBatch as $payment) {
            $currentOrderCode = $payment->orderCode;
            $order = $this->order->where('code', $currentOrderCode)->first();

            // 1. Dapatkan riwayat pembayaran untuk mengembalikan saldo bank
            $histories = $payment->paymentHistory;

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
                \App\Models\Mutation::where('transactionCode', $payment->code)
                    ->where('userBankCode', $bankCode)
                    ->where('description', 'like', '%' . $currentOrderCode . '%')
                    ->forceDelete();

                // Hapus history record (hard delete)
                $history->forceDelete();
            }

            // 2. Reset or delete VendorPayment record
            if ($payment->nota_number) {
                // If it has a nota_number, we must preserve it!
                // Reset paid_amount, remaining_amount, and payment_status to pending
                $payment->update([
                    'paid_amount' => 0,
                    'remaining_amount' => $payment->amount,
                    'payment_status' => 'pending',
                    'code' => null, // clear the batch payment code since payment is cancelled!
                ]);
            } else {
                // If no nota_number, safe to force delete
                $payment->forceDelete();
            }

            // 3. Kembalikan status Order
            if ($order) {
                $isInvoiced = \App\Models\Finance\InvoiceDetail::where('orderCode', $currentOrderCode)
                    ->whereNull('deleted_at')
                    ->exists();

                $order->update([
                    'status' => $isInvoiced ? 5 : 4,
                ]);
            }

            $this->logActivity($title, $payment, 'Cancel Payment');
        }
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

