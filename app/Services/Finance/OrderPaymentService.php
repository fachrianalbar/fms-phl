<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\Finance\VendorPayment;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Traits\LogActivity;
use Carbon\Carbon;

class OrderPaymentService
{
    use LogActivity;

    protected $service;
    protected $order;
    protected $orderPaymentHistory;
    protected $mutation;

    public function __construct(OrderPayment $service, Order $order, OrderPaymentHistory $orderPaymentHistory, Mutation $mutation)
    {
        $this->service = $service;
        $this->order = $order;
        $this->orderPaymentHistory = $orderPaymentHistory;
        $this->mutation = $mutation;
    }

    public function findAll()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('type', 'Individual');
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment'])->get();
    }

    public function getById(string $id)
    {
        return $this->order->where('id', $id)->with(['customer', 'orderPayment', 'orderPaymentHistory'])->first();
    }

    public function store($request, $title)
    {
        $orderPayment = $this->service->where('orderCode', $request->orderCode)->first();

        $status = 0;

        if ($request->type == 'Full') {
            $status = 1;
        }

        $payment = $status == 1 ? $request->total : $request->paymentAmount;

        if (!$orderPayment) {
            $data = $this->service->create([
                'code' => GenerateCode::generateCode('FOP'),
                'orderCode' => $request->orderCode,
                'cost' => $request->cost,
                'pph' => $request->pph,
                'total' => $payment,
            ]);

            $this->logActivity($title, $data, 'Create');
        } else {
            $this->logActivity($title, $orderPayment, 'Before Update');

            if ($request->type == 'Dp') {
                if ($orderPayment->total + $request->paymentAmount == $orderPayment->cost + $orderPayment->pph) {
                    $orderPayment->update([
                        "status" => 1
                    ]);
                }
            } else {
                $orderPayment->update([
                    "status" => 1
                ]);
            }

            $orderPayment->update([
                'total' => $status == 1 ? $request->total + $orderPayment->total : $request->paymentAmount + $orderPayment->total,
            ]);

            $this->logActivity($title, $orderPayment->refresh(), 'After Update');
        }

        LiveMutationHelper::updateLiveMutation($request->userBankCode, $payment, 'debit');

        $orderPaymentHistory =  $this->orderPaymentHistory->create([
            'code' => GenerateCode::generateCode('FOPH'),
            'orderCode' => $request->orderCode,
            'paymentType' => $request->type,
            'total' =>  $payment,
            'date' => $request->date,
            'description' => $request->description,
            'userBankCode' => $request->userBankCode
        ]);

        $mutation = $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'date' => now(),
            'description' => 'Order payment with amount ' . number_format($payment, 0, ',', '.'),
            'nominal' => $payment,
            'type' => "In",
            'transactionCode' => $orderPaymentHistory->code,
            'transactionTypeCode' => "FTT250306114178", // Order Payment
        ]);

        $this->logActivity('Order Payment History', $orderPaymentHistory, 'Create');

        $this->logActivity('Mutation', $mutation, 'Create');
    }

    public function orderPaymentDetail($orderCode)
    {
        $data = $this->order->where('code', $orderCode)->with(['customer', 'orderPayment', 'orderPaymentHistory', 'cost'])->first();

        $cost = 0;
        foreach ($data->cost as $item) {
            $cost += $item->nominal;
        }

        $pph = isset($data->customer->pph) ? $cost * ($data->customer->pph / 100) : 0;
        $payment = $data->orderPayment->total ?? 0;
        $total = $cost + $pph;


        return [
            "cost" => $cost,
            "pph" => $pph,
            "total" => $total - $payment,
            "payment" => $payment
        ];
    }
}
