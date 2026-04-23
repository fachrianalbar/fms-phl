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
}
