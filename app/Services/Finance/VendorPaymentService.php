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
            $q->where('type', 'External');
        })
            ->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'vendorPayments'])
            ->orderBy('orderDate', 'asc')
            ->get();
    }

    public function store($request, $title)
    {
        $order = $this->order->where('code', $request->orderCode)->first();
        $userBank = $this->userBank->where('code', $request->userBankCode)->first();

        // Cek apakah sudah ada vendor payment untuk order ini
        $vendorPayment = $this->service->where('orderCode', $request->orderCode)->first();

        // Tagihan awal dari order
        $billingAmount = (float) ($order->personalVendorPrice ?? 0);

        if (! $vendorPayment) {
            // Pertama kali: buat vendor payment baru
            // amount = tagihan awal, bukan jumlah pembayaran!
            $vendorPayment = $this->service->create([
                'date' => $request->date,
                'amount' => $billingAmount,  // Tagihan awal
                'paid_amount' => 0,
                'remaining_amount' => $billingAmount,
                'payment_status' => 'pending',
                'description' => $request->description,
                'orderCode' => $request->orderCode,
                'code' => GenerateCode::generateCode('FVP'),
            ]);
        }

        // Validasi jumlah pembayaran tidak melebihi remaining
        $remainingAmount = (float) $vendorPayment->remaining_amount;
        $paymentAmount = (int) $request->amount;  // Ini jumlah pembayaran dari request

        if ($paymentAmount > $remainingAmount) {
            throw new \Exception('Jumlah pembayaran tidak boleh melebihi sisa tagihan');
        }

        // Update paid amount dan remaining amount
        $newPaidAmount = ((float) $vendorPayment->paid_amount ?? 0) + $paymentAmount;
        $newRemainingAmount = $billingAmount - $newPaidAmount;

        // Tentukan payment status
        $paymentStatus = 'pending';
        if ($newRemainingAmount <= 0) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        }

        // Update vendor payment record
        $vendorPayment->update([
            'paid_amount' => $newPaidAmount,
            'remaining_amount' => max(0, $newRemainingAmount),
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
        if ($userBank) {
            LiveMutationHelper::updateLiveMutation($userBank->code, $paymentAmount, 'credit');
        }

        // Create mutation record for accounting
        $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'nominal' => $paymentAmount,
            'type' => 'Out', // Out untuk pengeluaran
            'date' => $request->date,
            'description' => 'Vendor Payment for Order ' . $order->code . ' with amount ' . number_format($paymentAmount, 0, '.', ','),
            'transactionTypeCode' => 'FTT251208001130', // Vendor Payment transaction type
        ]);

        // Update order status hanya jika sudah full bayar
        if ($paymentStatus === 'paid') {
            $order->update([
                'status' => 6, // Status paid
            ]);
        }

        $this->logActivity($title, $vendorPayment, 'Create');
    }
}
