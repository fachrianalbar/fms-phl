<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;

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

    public function findAllIsDoZero()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('isDo', 0);
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment', 'cost'])->get();
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
        $billingCost = (float) ($request->cost ?? 0);
        $billingAdditionalCost = (float) ($request->additional_cost ?? 0);
        $billingPpn = (float) ($request->ppn ?? 0);
        $billingPph = (float) ($request->pph ?? 0);
        $billingTotal = $billingCost + $billingAdditionalCost + $billingPpn - $billingPph;

        if (! $orderPayment) {
            $data = $this->service->create([
                'code' => GenerateCode::generateCode('FOP'),
                'orderCode' => $request->orderCode,
                'cost' => $billingCost,
                'additional_cost' => $billingAdditionalCost,
                'ppn' => $billingPpn,
                'pph' => $billingPph,
                'total' => $payment,
                'status' => ((float) $payment >= $billingTotal) ? 1 : $status,
            ]);

            $this->logActivity($title, $data, 'Create');
        } else {
            $this->logActivity($title, $orderPayment, 'Before Update');

            $newTotal = $status == 1
                ? (float) $request->total + (float) $orderPayment->total
                : (float) $request->paymentAmount + (float) $orderPayment->total;

            $orderPayment->update([
                'cost' => $billingCost,
                'additional_cost' => $billingAdditionalCost,
                'ppn' => $billingPpn,
                'pph' => $billingPph,
                'total' => $newTotal,
                'status' => $newTotal >= $billingTotal ? 1 : 0,
            ]);

            $this->logActivity($title, $orderPayment->refresh(), 'After Update');
        }

        LiveMutationHelper::updateLiveMutation($request->userBankCode, $payment, 'debit');

        $orderPaymentHistory = $this->orderPaymentHistory->create([
            'code' => GenerateCode::generateCode('FOPH'),
            'orderCode' => $request->orderCode,
            'paymentType' => $request->type,
            'total' => $payment,
            'date' => $request->date,
            'description' => $request->description,
            'userBankCode' => $request->userBankCode,
        ]);

        $mutation = $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'date' => now(),
            'description' => 'Order payment with amount ' . number_format($payment, 0, ',', '.'),
            'nominal' => $payment,
            'type' => 'In',
            'transactionCode' => $orderPaymentHistory->code,
            'transactionTypeCode' => 'FTT250306114178', // Order Payment
        ]);

        $this->logActivity('Order Payment History', $orderPaymentHistory, 'Create');

        $this->logActivity('Mutation', $mutation, 'Create');
    }

    public function orderPaymentDetail($orderCode)
    {
        $data = $this->order->where('code', $orderCode)
            ->with(['customer', 'orderPayment', 'orderPaymentHistory', 'cost'])
            ->first();

        $cost = (float) ($data->routeAmount ?? 0);

        if (isset($data->orderPayment)) {
            $additional_cost = isset($data->orderPayment->additional_cost) 
                ? (float) $data->orderPayment->additional_cost 
                : (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');

            $ppn = isset($data->orderPayment->ppn) 
                ? (float) $data->orderPayment->ppn 
                : (isset($data->customer->ppn) ? ($cost + $additional_cost) * ($data->customer->ppn / 100) : 0);

            $pph = isset($data->orderPayment->pph) 
                ? (float) $data->orderPayment->pph 
                : (isset($data->customer->pph) ? ($cost + $additional_cost) * ($data->customer->pph / 100) : 0);
        } else {
            $additional_cost = (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
            $ppn = isset($data->customer->ppn) ? ($cost + $additional_cost) * ($data->customer->ppn / 100) : 0;
            $pph = isset($data->customer->pph) ? ($cost + $additional_cost) * ($data->customer->pph / 100) : 0;
        }

        $payment = $data->orderPayment->total ?? 0;
        $grandTotal = $cost + $additional_cost + $ppn - $pph;

        return [
            'cost' => $cost,
            'additional_cost' => $additional_cost,
            'ppn' => $ppn,
            'pph' => $pph,
            'grand_total' => $grandTotal,
            'payment' => $payment,
            'total' => $grandTotal - $payment,
        ];
    }
}
