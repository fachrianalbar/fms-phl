<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\UserBank;
use App\Models\Finance\VendorPayment;
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

    public function __construct(VendorPayment $vendorPayment, Order $order, UserBank $userBank, Mutation $mutation)
    {
        $this->service = $vendorPayment;
        $this->order = $order;
        $this->userBank = $userBank;
        $this->mutation = $mutation;
    }

    public function findAll()
    {
        return $this->order->whereHas('fleet.company', function ($q) {
            $q->where('type', 'External');
        })
            ->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'vendorPayments'])
            ->get();
    }

    public function store($request, $title)
    {
        $order = $this->order->where('code', $request->orderCode)->first();

        // Get userBank from request instead of fleet relation
        $userBank = $this->userBank->where('code', $request->userBankCode)->first();

        // Create vendor payment record
        $data = $this->service->create([
            'date' => $request->date,
            'amount' => (int) $request->amount,
            'description' => $request->description,
            'orderCode' => $request->orderCode,
            'code' => GenerateCode::generateCode('FVP'),
        ]);

        // Update LiveMutation with CREDIT (pengeluaran uang)
        if ($userBank) {
            LiveMutationHelper::updateLiveMutation($userBank->code, (int) $request->amount, 'credit');
        }

        // Create mutation record for accounting
        $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'nominal' => (int) $request->amount,
            'type' => 'Out', // Out untuk pengeluaran
            'date' => $request->date,
            'description' => 'Vendor Payment for Order '.$order->code.' with amount '.number_format((int) $request->amount, 0, '.', ','),
            'transactionTypeCode' => 'FTT251208001130', // Vendor Payment transaction type
        ]);

        // Update order status to 6 (paid)
        $order->update([
            'status' => 6,
        ]);

        $this->logActivity($title, $data, 'Create');
    }
}
