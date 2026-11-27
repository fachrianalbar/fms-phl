<?php

namespace App\Services\Report;

use App\Models\Finance\Invoice;
use App\Models\Operational\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class OverallReportService
{
    /**
     * Query dasar Order dengan eager-load lengkap untuk Laporan Keseluruhan.
     *
     * @param  array{startDate: ?string, endDate: ?string, customerCode: ?string, fleetCompanyCode: ?string, factoryStatus: ?string, vendorStatus: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = Order::query()
            ->with([
                'customer.company',
                'material',
                'route.originLocation',
                'route.destinationLocation',
                'fleet.company',
                'invoiceDetail.invoice.details',
                'invoiceDetail.invoice.payments',
                'invoiceDetail.invoice.claims',
                'vendorPayments.paymentHistory',
                'orderPayment.paymentHistory',
                'cost.costComponent',
                'orderCommissions.costComponent',
                'orderCommissions.commissionReport',
            ])
            ->whereNull('order.deleted_at');

        if ($filters['startDate']) {
            $query->whereDate('order.orderDate', '>=', $filters['startDate']);
        }

        if ($filters['endDate']) {
            $query->whereDate('order.orderDate', '<=', $filters['endDate']);
        }

        if ($filters['customerCode']) {
            $query->where('order.customerCode', $filters['customerCode']);
        }

        if ($filters['fleetCompanyCode']) {
            $fleetCompanyCode = $filters['fleetCompanyCode'];
            $query->whereHas('fleet', fn (Builder $q) => $q->where('fleetCompanyCode', $fleetCompanyCode));
        }

        if ($filters['factoryStatus'] === 'paid') {
            $query->whereHas('invoiceDetail.invoice', fn (Builder $q) => $q->where('status', Invoice::STATUS_FULL));
        } elseif ($filters['factoryStatus'] === 'unpaid') {
            $query->where(function (Builder $q) {
                $q->whereDoesntHave('invoiceDetail.invoice')
                    ->orWhereHas('invoiceDetail.invoice', fn (Builder $inv) => $inv->where('status', '<>', Invoice::STATUS_FULL));
            });
        }

        if ($filters['vendorStatus'] === 'paid') {
            $query->where(function (Builder $q) {
                $q->whereHas('vendorPayments', fn (Builder $vp) => $vp->where('payment_status', 'paid'))
                    ->orWhereHas('orderPayment', fn (Builder $op) => $op->where('status', 2));
            });
        } elseif ($filters['vendorStatus'] === 'unpaid') {
            $query->whereDoesntHave('vendorPayments', fn (Builder $vp) => $vp->where('payment_status', 'paid'))
                ->whereDoesntHave('orderPayment', fn (Builder $op) => $op->where('status', 2));
        }

        return $query;
    }

    /**
     * Hitung 34 kolom laporan keseluruhan untuk 1 order secara konsisten (DataTable, Excel, PDF).
     *
     * @return array<string, mixed>
     */
    public function buildRow(Order $order): array
    {
        // 1. Info Order (8 kolom)
        $orderDate = $order->orderDate ? Carbon::parse($order->orderDate)->format('d-m-Y') : '-';
        $plateNumber = $order->plateNumber ?: ($order->fleet?->plateNumber ?? '-');
        $customerName = $order->customer?->name ?? '-';
        $materialName = $order->material?->name ?? '-';
        $origin = $order->route?->originLocation?->name ?? '-';
        $destination = $order->route?->destinationLocation?->name ?? '-';
        $fleetOwner = $order->fleet?->company?->name ?? '-';
        $expeditionName = $order->customer?->company?->name ?? '-';

        // 2. Blok Pabrik / Piutang (10 kolom)
        $factoryTonase = (float) ($order->qty ?? 0);
        $factoryOa = (float) ($order->price ?? 0);
        $factoryAmount = (float) ($order->routeAmount ?: ($factoryTonase * $factoryOa));

        $invoice = $order->invoiceDetail?->invoice;
        $invoiceOrderCount = $invoice ? max($invoice->details->count(), 1) : 1;
        $invoiceBase = $invoice ? (float) ($invoice->routeAmount ?: $invoice->invoiceAmount) : 0.0;
        $shareRatio = ($invoice && $invoiceBase > 0 && $invoiceOrderCount > 1)
            ? min(1.0, $factoryAmount / $invoiceBase)
            : ($invoiceOrderCount > 1 ? 1 / $invoiceOrderCount : 1.0);

        $factoryTax = $invoice ? round((float) ($invoice->pphAmount ?? 0) * $shareRatio, 2) : 0.0;
        $factoryClaim = $invoice ? round((float) $invoice->claims->sum('amount') * $shareRatio, 2) : 0.0;
        $factoryTotal = $factoryAmount - $factoryTax - $factoryClaim;
        $factoryInvoiceNo = $invoice ? ($invoice->invoiceNumber ?: ($invoice->code ?: '-')) : '-';

        $factoryDp = 0.0;
        $factoryPaymentDate = '-';
        $factoryPaid = false;

        if ($invoice) {
            $payments = $invoice->payments->sortBy('paymentDate')->values();
            $factoryPaid = (int) $invoice->status === Invoice::STATUS_FULL;

            if ($payments->isNotEmpty()) {
                $lastPayDate = $payments->max('paymentDate');
                $factoryPaymentDate = $lastPayDate ? Carbon::parse($lastPayDate)->format('d-m-Y') : '-';

                if (! $factoryPaid) {
                    $factoryDp = round((float) $payments->sum('amount') * $shareRatio, 2);
                } elseif ($payments->count() > 1) {
                    $factoryDp = round((float) $payments->slice(0, -1)->sum('amount') * $shareRatio, 2);
                }
            }
        }

        $factoryStatus = $factoryPaid ? 'Lunas' : 'Belum Lunas';

        // 3. Blok Komisi (4 kolom)
        $commissions = $order->orderCommissions;
        $commissionNames = [];
        $commissionAmount = 0.0;
        $commissionPaymentDate = '-';
        $commissionStatus = '-';

        if ($commissions->isNotEmpty()) {
            $commissionNames = $commissions
                ->map(fn ($c) => $c->costComponent?->name)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $commissionAmount = (float) $commissions->sum('amount');

            $latestCommDate = $commissions
                ->map(fn ($c) => $c->payment_date?->format('Y-m-d') ?? $c->commissionReport?->payment_date?->format('Y-m-d'))
                ->filter()
                ->max();
            $commissionPaymentDate = $latestCommDate ? Carbon::parse($latestCommDate)->format('d-m-Y') : '-';

            $allPaid = $commissions->every(function ($c) {
                $st = strtolower((string) $c->status);

                return in_array($st, ['paid', 'lunas', 'processed', '1'], true) || $c->payment_date !== null;
            });
            $commissionStatus = $commissionAmount > 0 ? ($allPaid ? 'Lunas' : 'Belum Lunas') : '-';
        } else {
            $commissionCosts = $order->cost->filter(function ($cost) {
                $compName = strtolower((string) ($cost->componentName ?: $cost->costComponent?->name));

                return str_contains($compName, 'komisi');
            });

            if ($commissionCosts->isNotEmpty()) {
                $commissionNames = $commissionCosts
                    ->map(fn ($c) => $c->componentName ?: $c->costComponent?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $commissionAmount = (float) $commissionCosts->sum('nominal');
                $commissionStatus = $commissionAmount > 0 ? 'Belum Lunas' : '-';
            }
        }

        $commissionName = $commissionNames !== [] ? implode(', ', $commissionNames) : '-';

        // 4. Blok Customer / Nota Pembayaran Vendor (10 kolom)
        $vendorTonase = (float) ($order->qty ?? 0);
        $vendorOa = (float) ($order->vendorPriceSingle ?: ($order->personalVendorPriceSingle ?: 0));
        $vendorReturnDate = $order->returnDate ? Carbon::parse($order->returnDate)->format('d-m-Y') : '-';

        $vendorPayment = $order->vendorPayments->first();
        $orderPayment = $order->orderPayment;

        if ($vendorPayment) {
            $vendorAmount = (float) ($vendorPayment->amount ?? 0);
            $vendorTax = (float) ($vendorPayment->pph_amount ?? 0);
            $vendorClaim = (float) ($vendorPayment->claim_amount ?? 0);
            $vendorTotal = $vendorAmount - $vendorTax - $vendorClaim;
            $vendorNotaNo = $vendorPayment->nota_number ?: ($vendorPayment->code ?: '-');
            $vendorPaid = strtolower((string) $vendorPayment->payment_status) === 'paid';

            $histories = $vendorPayment->paymentHistory->sortBy('payment_date')->values();
            $lastVendorDate = $histories->max('payment_date');
            $vendorPaymentDate = $lastVendorDate ? Carbon::parse($lastVendorDate)->format('d-m-Y') : '-';

            if (! $vendorPaid) {
                $vendorDp = (float) ($vendorPayment->paid_amount ?? $histories->sum('amount'));
            } elseif ($histories->count() > 1) {
                $vendorDp = (float) $histories->slice(0, -1)->sum('amount');
            } else {
                $vendorDp = 0.0;
            }

            $vendorStatus = $vendorPaid ? 'Lunas' : 'Belum Lunas';
        } elseif ($orderPayment) {
            $vendorAmount = (float) ($orderPayment->cost ?? 0) + (float) ($orderPayment->additional_cost ?? 0);
            $vendorTax = (float) ($orderPayment->pph ?? 0);
            $vendorClaim = (float) ($orderPayment->claim ?? 0);
            $vendorTotal = (float) ($orderPayment->total ?: ($vendorAmount - $vendorTax - $vendorClaim));
            $vendorNotaNo = $orderPayment->nota_number ?: ($orderPayment->code ?: '-');
            $vendorPaid = (int) $orderPayment->status === 2;

            $histories = $orderPayment->paymentHistory->sortBy('date')->values();
            $lastVendorDate = $histories->max('date');
            $vendorPaymentDate = $lastVendorDate ? Carbon::parse($lastVendorDate)->format('d-m-Y') : '-';

            if (! $vendorPaid) {
                $vendorDp = (float) $histories->sum('nominal');
            } elseif ($histories->count() > 1) {
                $vendorDp = (float) $histories->slice(0, -1)->sum('nominal');
            } else {
                $vendorDp = 0.0;
            }

            $vendorStatus = $vendorPaid ? 'Lunas' : 'Belum Lunas';
        } else {
            $vendorAmount = (float) ($order->vendorPrice ?: ($order->personalVendorPrice ?: ($vendorTonase * $vendorOa)));
            $vendorTax = 0.0;
            $vendorTotal = $vendorAmount;
            $vendorNotaNo = '-';
            $vendorDp = 0.0;
            $vendorPaymentDate = '-';
            $vendorStatus = 'Belum Lunas';
        }

        // 5. Ringkasan (2 kolom)
        $profit = self::calculateProfit($factoryTotal, $commissionAmount, $vendorTotal);
        $notesParts = array_values(array_filter([
            trim((string) ($order->notes ?? '')),
            trim((string) ($order->returnDescription ?? '')),
        ], fn (string $v) => $v !== ''));
        $notes = $notesParts !== [] ? implode(' | ', $notesParts) : '-';

        return [
            'order_date' => $orderDate,
            'plate_number' => $plateNumber,
            'customer_name' => $customerName,
            'material_name' => $materialName,
            'origin' => $origin,
            'destination' => $destination,
            'fleet_owner' => $fleetOwner,
            'expedition_name' => $expeditionName,
            'factory_tonase' => $factoryTonase,
            'factory_oa' => $factoryOa,
            'factory_amount' => $factoryAmount,
            'factory_tax' => $factoryTax,
            'factory_claim' => $factoryClaim,
            'factory_total' => $factoryTotal,
            'factory_invoice_no' => $factoryInvoiceNo,
            'factory_dp' => $factoryDp,
            'factory_payment_date' => $factoryPaymentDate,
            'factory_status' => $factoryStatus,
            'commission_name' => $commissionName,
            'commission_amount' => $commissionAmount,
            'commission_payment_date' => $commissionPaymentDate,
            'commission_status' => $commissionStatus,
            'vendor_tonase' => $vendorTonase,
            'vendor_oa' => $vendorOa,
            'vendor_amount' => $vendorAmount,
            'vendor_tax' => $vendorTax,
            'vendor_total' => $vendorTotal,
            'vendor_return_date' => $vendorReturnDate,
            'vendor_nota_no' => $vendorNotaNo,
            'vendor_dp' => $vendorDp,
            'vendor_payment_date' => $vendorPaymentDate,
            'vendor_status' => $vendorStatus,
            'profit' => $profit,
            'notes' => $notes,
        ];
    }

    /**
     * Rumus Untung sesuai sheet Excel:
     * Total Jumlah Pabrik - Komisi - Total Jumlah Vendor.
     */
    public static function calculateProfit(float $factoryTotal, float $commissionAmount, float $vendorTotal): float
    {
        return round($factoryTotal - $commissionAmount - $vendorTotal, 2);
    }
}
