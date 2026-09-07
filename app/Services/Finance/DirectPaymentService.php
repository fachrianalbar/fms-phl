<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Traits\LogActivity;

class DirectPaymentService
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

    /**
     * Semua order milik customer individual (legacy, tidak dipakai menu aktif).
     */
    public function findAll()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('type', 'Individual');
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment'])->get();
    }

    /**
     * Order customer non-DO yang pembayarannya dicatat langsung per order.
     */
    public function findAllIsDoZero()
    {
        return $this->order->whereHas('customer', function ($q) {
            $q->where('isDo', 0);
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderPayment', 'cost'])->get();
    }

    public function getById(string $id)
    {
        return $this->order->where('id', $id)->with(['customer', 'orderPayment', 'orderPaymentHistory', 'cost'])->first();
    }

    /**
     * Hitung nominal PPN/PPH dari input form.
     * Pajak dapat diisi sebagai persentase (dihitung dari subtotal)
     * atau langsung sebagai nominal.
     *
     * @return array{nominal: float, percent: float|null}
     */
    private function resolveTax(?string $type, $percentInput, $nominalInput, float $subtotal): array
    {
        if ($type === 'percent') {
            $percent = (float) ($percentInput ?? 0);

            if ($percent > 0) {
                return [
                    'nominal' => (float) round($subtotal * $percent / 100),
                    'percent' => $percent,
                ];
            }

            return ['nominal' => 0, 'percent' => null];
        }

        return [
            'nominal' => (float) ($nominalInput ?? 0),
            'percent' => null,
        ];
    }

    public function store($request, $title)
    {
        $orderPayment = $this->service->where('orderCode', $request->orderCode)->first();

        $billingCost = (float) ($request->cost ?? 0);
        $billingAdditionalCost = (float) ($request->additional_cost ?? 0);
        $billingSubtotal = $billingCost + $billingAdditionalCost;

        // PPN / PPH: persentase atau nominal
        $ppn = $this->resolveTax($request->ppn_type, $request->ppn_percent, $request->ppn, $billingSubtotal);
        $pph = $this->resolveTax($request->pph_type, $request->pph_percent, $request->pph, $billingSubtotal);

        $billingPpn = $ppn['nominal'];
        $billingPpnPercent = $ppn['percent'];
        $billingPph = $pph['nominal'];
        $billingPphPercent = $pph['percent'];

        // Biaya Claim (pengurang tagihan)
        $billingClaim = (float) ($request->claim ?? 0);
        $billingClaimDescription = $request->claim_description ?? null;

        $billingTotal = $billingSubtotal + $billingPpn - $billingPph - $billingClaim;

        // Nominal pembayaran: mendukung DP/cicilan (partial) maupun pelunasan
        $payment = (float) ($request->paymentAmount ?? 0);

        if ($payment <= 0) {
            throw new \InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        $alreadyPaid = $orderPayment ? (float) $orderPayment->total : 0;
        $newTotal = $alreadyPaid + $payment;

        $paymentType = $request->type;
        if (! in_array($paymentType, ['Full', 'Dp'])) {
            $paymentType = $payment >= ($billingTotal - $alreadyPaid) ? 'Full' : 'Dp';
        }

        $billingData = [
            'cost' => $billingCost,
            'additional_cost' => $billingAdditionalCost,
            'claim' => $billingClaim,
            'claim_description' => $billingClaimDescription,
            'ppn' => $billingPpn,
            'ppn_percent' => $billingPpnPercent,
            'pph' => $billingPph,
            'pph_percent' => $billingPphPercent,
            'total' => $newTotal,
            'status' => $newTotal >= $billingTotal ? 1 : 0,
        ];

        if (! $orderPayment) {
            $data = $this->service->create(array_merge([
                'code' => GenerateCode::generateUniqueCode('FOP', 'order_payment'),
                'orderCode' => $request->orderCode,
            ], $billingData));

            $this->logActivity($title, $data, 'Create');
        } else {
            $this->logActivity($title, $orderPayment, 'Before Update');

            $orderPayment->update($billingData);

            $this->logActivity($title, $orderPayment->refresh(), 'After Update');
        }

        LiveMutationHelper::updateLiveMutation($request->userBankCode, $payment, 'debit');

        // Riwayat pembayaran: setiap transaksi tercatat lengkap
        // dengan snapshot pajak dan claim yang dipakai saat pembayaran dibuat
        $orderPaymentHistory = $this->orderPaymentHistory->create([
            'code' => GenerateCode::generateUniqueCode('FOPH', 'order_payment_history'),
            'orderCode' => $request->orderCode,
            'paymentType' => $paymentType,
            'claim' => $billingClaim,
            'claim_description' => $billingClaimDescription,
            'ppn' => $billingPpn,
            'ppn_percent' => $billingPpnPercent,
            'pph' => $billingPph,
            'pph_percent' => $billingPphPercent,
            'total' => $payment,
            'date' => $request->date,
            'description' => $request->description,
            'userBankCode' => $request->userBankCode,
        ]);

        $mutation = $this->mutation->create([
            'code' => GenerateCode::generateUniqueCode('FMT', 'mutation'),
            'userBankCode' => $request->userBankCode,
            'date' => now(),
            'description' => 'Direct payment for order ' . $request->orderCode . ' with amount ' . number_format($payment, 0, ',', '.'),
            'nominal' => $payment,
            'type' => 'In',
            'transactionCode' => $orderPaymentHistory->code,
            'transactionTypeCode' => 'FTT250306114178', // Order Payment
        ]);

        $this->logActivity('Order Payment History', $orderPaymentHistory, 'Create');

        $this->logActivity('Mutation', $mutation, 'Create');
    }

    /**
     * Ringkasan tagihan order untuk form pembayaran.
     * Menyertakan persentase PPN/PPH tersimpan dan default customer
     * agar form dapat mengisi mode persentase secara otomatis.
     */
    public function orderPaymentDetail($orderCode)
    {
        $data = $this->order->where('code', $orderCode)
            ->with([
                'customer',
                'orderPayment',
                'orderPaymentHistory.userBank.bank',
                'cost.costComponent',
                'fleet',
                'driver',
                'route.originLocation',
                'route.destinationLocation',
            ])
            ->first();

        $cost = (float) ($data->routeAmount ?? 0);

        $customerPpnPercent = isset($data->customer->ppn) ? (float) $data->customer->ppn : 0;
        $customerPphPercent = isset($data->customer->pph) ? (float) $data->customer->pph : 0;

        if (isset($data->orderPayment)) {
            $additional_cost = isset($data->orderPayment->additional_cost)
                ? (float) $data->orderPayment->additional_cost
                : (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');

            $ppn = isset($data->orderPayment->ppn)
                ? (float) $data->orderPayment->ppn
                : ($customerPpnPercent > 0 ? ($cost + $additional_cost) * ($customerPpnPercent / 100) : 0);

            $pph = isset($data->orderPayment->pph)
                ? (float) $data->orderPayment->pph
                : ($customerPphPercent > 0 ? ($cost + $additional_cost) * ($customerPphPercent / 100) : 0);

            $ppn_percent = isset($data->orderPayment->ppn_percent) ? (float) $data->orderPayment->ppn_percent : null;
            $pph_percent = isset($data->orderPayment->pph_percent) ? (float) $data->orderPayment->pph_percent : null;
        } else {
            $additional_cost = (float) $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
            $ppn = $customerPpnPercent > 0 ? ($cost + $additional_cost) * ($customerPpnPercent / 100) : 0;
            $pph = $customerPphPercent > 0 ? ($cost + $additional_cost) * ($customerPphPercent / 100) : 0;
            $ppn_percent = null;
            $pph_percent = null;
        }

        $claim = 0;
        $claim_description = '';
        if (isset($data->orderPayment)) {
            $claim = (float) ($data->orderPayment->claim ?? 0);
            $claim_description = $data->orderPayment->claim_description ?? '';
        }

        $payment = $data->orderPayment->total ?? 0;
        $grandTotal = $cost + $additional_cost + $ppn - $pph - $claim;

        $origin = $data->route->originLocation->name ?? '-';
        $dest = $data->route->destinationLocation->name ?? '-';

        $routeTypeCode = $data->route->routeTypeCode ?? '-';
        $routeTypeLabel = 'Kubik';
        if ($routeTypeCode === 'TONASE') {
            $routeTypeLabel = 'Tonase';
        } elseif ($routeTypeCode === 'TRIP') {
            $routeTypeLabel = 'Trip';
        }

        $onChargeCosts = ($data->cost ?? collect())->filter(fn($c) => strtolower($c->type ?? '') === 'on charge');
        $additionalCostsBreakdown = $onChargeCosts->map(fn($c) => [
            'component' => $c->costComponent->name ?? 'Biaya Tambahan',
            'description' => $c->description ?? '-',
            'nominal' => (float) ($c->nominal ?? 0),
        ])->values();

        $historyList = ($data->orderPaymentHistory ?? collect())->sortByDesc('id')->map(function ($h) {
            $bankStr = '-';
            if ($h->userBank) {
                $accNo = $h->userBank->accountNumber ?? '';
                $bName = $h->userBank->bank->name ?? '';
                $accName = $h->userBank->accountName ?? '';
                $bankStr = trim("{$accNo} - {$bName} a/n {$accName}", ' -');
            }

            return [
                'id' => $h->id,
                'date' => $h->date ? \Carbon\Carbon::parse($h->date)->format('d/m/Y') : '-',
                'type' => $h->paymentType ?? 'Full',
                'bank' => $bankStr,
                'ppn' => (float) ($h->ppn ?? 0),
                'ppn_percent' => $h->ppn_percent !== null ? (float) $h->ppn_percent : null,
                'pph' => (float) ($h->pph ?? 0),
                'pph_percent' => $h->pph_percent !== null ? (float) $h->pph_percent : null,
                'claim' => (float) ($h->claim ?? 0),
                'claim_description' => $h->claim_description ?? '',
                'description' => $h->description ?? '-',
                'total' => (float) ($h->total ?? 0),
            ];
        })->values();

        // Calculate status
        $status = 'Belum Bayar';
        $statusCode = 'unpaid';
        if ($payment > 0) {
            if ($payment == $grandTotal) {
                $status = 'Lunas';
                $statusCode = 'paid';
            } elseif ($payment > $grandTotal) {
                $status = 'Kelebihan Bayar';
                $statusCode = 'overpaid';
            } else {
                $status = 'Belum Lunas';
                $statusCode = 'partial';
            }
        }

        return [
            'order_id' => $data->id,
            'order_code' => $data->code,
            'order_date' => $data->orderDate ? \Carbon\Carbon::parse($data->orderDate)->format('d/m/Y') : '-',
            'customer_name' => $data->customer->name ?? '-',
            'plate_number' => $data->fleet->plateNumber ?? '-',
            'driver_name' => $data->driver->name ?? '-',
            'shipment_number' => $data->shipmentNumber ?? '-',
            'route_type_label' => $routeTypeLabel,
            'qty' => (float) ($data->qty ?? 0),
            'notes' => $data->notes ?? '',
            'route_origin' => $origin,
            'route_destination' => $dest,
            'status' => $status,
            'status_code' => $statusCode,
            'cost' => $cost,
            'additional_cost' => $additional_cost,
            'additional_costs' => $additionalCostsBreakdown,
            'claim' => $claim,
            'claim_description' => $claim_description,
            'ppn' => $ppn,
            'ppn_percent' => $ppn_percent,
            'pph' => $pph,
            'pph_percent' => $pph_percent,
            'customer_ppn_percent' => $customerPpnPercent,
            'customer_pph_percent' => $customerPphPercent,
            'grand_total' => $grandTotal,
            'payment' => $payment,
            'total' => $grandTotal - $payment,
            'histories' => $historyList,
        ];
    }
}
