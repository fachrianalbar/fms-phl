<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Master\Customer;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Carbon\Carbon;

class InvoiceService
{
    use LogActivity;

    protected $service;

    protected $order;

    protected $invoiceDetail;

    protected $customer;

    public function __construct(Invoice $invoice, Order $order, InvoiceDetail $invoiceDetail, Customer $customer)
    {
        $this->service = $invoice;
        $this->order = $order;
        $this->invoiceDetail = $invoiceDetail;
        $this->customer = $customer;
    }

    public function findAll()
    {
        return $this->service->with(['details'])->orderBy('invoiceDate', 'desc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'details.order.orderMaterial.material',
            'details.order.orderMaterial.unit',
            'details.order.cost',
            'details.order.customer',
            'details.order.fleet',
            'details.order.driver',
            'details.order.route.originLocation',
            'details.order.route.destinationLocation',
            'customer',
            'payments',
            'customer.pic',
        ])->first();
    }

    public function getOrder()
    {
        return $this->order
            ->where(function ($q) {
                $q->where('status', 4)
                    ->orWhereHas('customer', function ($q2) {
                        $q2->where('isDo', 0);
                    });
            })
            ->where('status', '!=', 5) // buang semua status 5
            ->with([
                'fleet',
                'fleet.type',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'orderType',
                'route.routeDetail',
            ])
            ->orderBy('created_at', 'desc');
    }

    public function store($request, $title, $selectedOrders)
    {
        $usePpn = (bool) ($request->input('usePpn') ?? false);

        $data = $this->service->create([
            'code' => GenerateCode::generateCode('INV'),
            'customerCode' => $request->customerCode,
            'invoiceNumber' => $request->invoiceNumber,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => Carbon::parse($request->invoiceDate)->addDays(2)->toDateString(),
            'notes' => $request->notes,
            'usePpn' => $usePpn,
            'status' => Invoice::STATUS_CREATE,
        ]);

        if (isset($request->order)) {
            foreach ($selectedOrders as $item) {
                $detail = $this->invoiceDetail->create([
                    'code' => GenerateCode::generateCode('INVD', true),
                    'invoiceCode' => $data->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5,
                ]);

                $this->logActivity('Invoice Detail', $detail, 'Create');
            }
        }
        // Update invoiceAmount (subtotal) and ppnAmount after creating invoice details
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
        ]);

        // Update invoice status after recalc
        try {
            $sumPayments = (int) $this->service->find($data->id)->payments()->sum('amount');
            $invoiceTotal = (int) ($totals['subtotal'] + $totals['ppn']);
            $nextStatus = Invoice::STATUS_CREATE;
            if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                $nextStatus = Invoice::STATUS_FULL;
            } elseif ($sumPayments > 0) {
                $nextStatus = Invoice::STATUS_PARTIAL;
            }
            $this->service->where('id', $data->id)->update(['status' => $nextStatus]);
        } catch (\Exception $e) {
            logger()->error('Failed to update invoice status after recalculation for invoice ' . $data->code . ': ' . $e->getMessage());
        }
        // Update invoice status after recalc
        try {
            $sumPayments = (int) $this->service->find($data->id)->payments()->sum('amount');
            $invoiceTotal = (int) ($totals['subtotal'] + $totals['ppn']);
            $nextStatus = Invoice::STATUS_CREATE;
            if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                $nextStatus = Invoice::STATUS_FULL;
            } elseif ($sumPayments > 0) {
                $nextStatus = Invoice::STATUS_PARTIAL;
            }
            $this->service->where('id', $data->id)->update(['status' => $nextStatus]);
        } catch (\Exception $e) {
            logger()->error('Failed to update invoice status after recalc for invoice ' . $data->code . ': ' . $e->getMessage());
        }
        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'invoiceNumber' => $request->invoiceNumber,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => Carbon::parse($request->invoiceDate)->addDays(2)->toDateString(),
            'notes' => $request->notes,
            'usePpn' => (bool) ($request->input('usePpn') ?? false),
        ]);

        // Recalculate invoice amount after update
        $data = $this->getById($id);
        $totals = $this->calculateInvoiceAmount($data);
        $this->service->where('id', $data->id)->update([
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            $this->order->where('code', $item->orderCode)->update([
                'status' => 4,
            ]);

            $this->invoiceDetail->where('id', $item->id)->delete();

            $this->logActivity('Invoice Detail', $item, 'Delete');
        }

        $this->service->where('id', $id)->delete();
    }

    public function getOrderDetail($id)
    {
        $data = $this->getById($id);
        $orderCodeArr = $this->invoiceDetail->where('invoiceCode', $data->code)->pluck('orderCode');

        return $this->order->whereIn('code', $orderCodeArr)->with([
            'fleetDriver.fleet',
            // 'fleetDriver.employee',
            'fleet',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'route.originLocation',
            'orderType',
            'route.routeDetail',
        ])->orderBy('created_at', 'desc')->get();
    }

    public function storeInvoiceDetail($request, $id, $selectedOrders)
    {
        $invoice = $this->getById($id);
        if (isset($request->order)) {

            foreach ($selectedOrders as $item) {
                $detail = $this->invoiceDetail->create([
                    'code' => GenerateCode::generateCode('INVD', true),
                    'invoiceCode' => $invoice->code,
                    'orderCode' => $item,
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5,
                ]);

                $this->logActivity('Invoice Detail', $detail, 'Create');
            }
            // update invoice and ppn amounts after adding details
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'ppnAmount' => $totals['ppn'],
            ]);

            // Update invoice status after recalc
            try {
                $sumPayments = (int) $this->service->find($invoice->id)->payments()->sum('amount');
                $invoiceTotal = (int) ($totals['subtotal'] + $totals['ppn']);
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($sumPayments > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after adding details for invoice ' . $invoice->code . ': ' . $e->getMessage());
            }
        }
    }

    public function destroyInvoiceDetail($id)
    {
        $order = $this->order->where('id', $id)->first();

        $this->order->where('id', $id)->update([
            'status' => 4,
        ]);

        $data = $this->invoiceDetail->where('orderCode', $order->code)->first();

        $this->logActivity('Invoice Detail', $data, 'Delete');

        $this->invoiceDetail->where('orderCode', $order->code)->delete();

        // update invoice amount after removing detail
        $invoice = $this->getById($data->invoiceCode ?? null);
        if ($invoice) {
            $totals = $this->calculateInvoiceAmount($invoice);
            $this->service->where('id', $invoice->id)->update([
                'invoiceAmount' => $totals['subtotal'],
                'ppnAmount' => $totals['ppn'],
            ]);

            // Update invoice status after recalc
            try {
                $sumPayments = (int) $this->service->find($invoice->id)->payments()->sum('amount');
                $invoiceTotal = (int) ($totals['subtotal'] + $totals['ppn']);
                $nextStatus = Invoice::STATUS_CREATE;
                if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                    $nextStatus = Invoice::STATUS_FULL;
                } elseif ($sumPayments > 0) {
                    $nextStatus = Invoice::STATUS_PARTIAL;
                }
                $this->service->where('id', $invoice->id)->update(['status' => $nextStatus]);
            } catch (\Exception $e) {
                logger()->error('Failed to update invoice status after removing details for invoice ' . $invoice->code . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Calculate invoice total based on details, allowances, tonase bonus, order costs and customer ppn.
     */
    public function calculateInvoiceAmount($invoiceOrId)
    {
        $invoice = $invoiceOrId instanceof \App\Models\Finance\Invoice ? $invoiceOrId : $this->getById($invoiceOrId);

        if (! $invoice) {
            return 0;
        }

        $subtotal = 0;

        foreach ($invoice->details as $detail) {
            // Prefer using loaded relation to avoid extra queries
            $order = ($detail->order ?? null);
            if (! $order) {
                $order = $this->order->where('code', $detail->orderCode)->with('cost')->first();
            }
            if (! $order) {
                continue;
            }

            // `routeAmount` is stored as total for the order (unit price * qty), use it directly
            $routeAmount = (float) ($order->routeAmount ?? 0);
            $subtotal += $routeAmount;

            // add On Charge order costs
            $onChargeCost = 0;
            if (isset($order->cost)) {
                foreach ($order->cost as $c) {
                    if (isset($c->type) && strtolower($c->type) === 'on charge') {
                        $onChargeCost += (int) $c->nominal;
                    }
                }
            }
            $subtotal += $onChargeCost;
        }

        $ppn = 0;
        $usePpn = $invoice->usePpn ?? true; // default true if not set
        if ($usePpn && $invoice->customer && isset($invoice->customer->ppn)) {
            $ppn = $subtotal * ($invoice->customer->ppn / 100);
        }

        $total = (int) round($subtotal + $ppn);

        return [
            'subtotal' => (int) round($subtotal),
            'ppn' => (int) round($ppn),
            'total' => $total,
        ];
    }

    public function invoiceNumberFormat($id, $invoiceDate = null)
    {
        $customer = $this->customer->where('id', $id)->with('company')->first();

        // Gunakan invoiceDate jika ada, jika tidak gunakan tanggal hari ini
        $dateToUse = $invoiceDate ? Carbon::parse($invoiceDate) : now();
        $currentYear = $dateToUse->year;
        $currentMonth = str_pad($dateToUse->month, 2, '0', STR_PAD_LEFT);

        // Ambil invoiceNumber terakhir milik customer yang bersangkutan di bulan dan tahun dari invoiceDate
        $lastInvoice = $this->service
            ->where('customerCode', $customer->code)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $dateToUse->month)
            ->orderByDesc('created_at')
            ->first();

        // Default increment = 1 jika belum ada invoice sebelumnya
        $lastNumber = 0;

        // Format: INV/{FORMAT-COMPANY}/{CODE-CUSTOMER}/{NO-URUT}/{BULAN}/{TAHUN}
        if ($lastInvoice && preg_match('/INV\/' . preg_quote($customer->company->format, '/') . '\/' . preg_quote($customer->code, '/') . '\/(\d{5})\//', $lastInvoice->invoiceNumber, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $increment = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        $companyFormat = $customer->company->format ?? 'DEFAULT';

        return 'INV/' . $companyFormat . '/' . $customer->code . '/' . $increment . '/' . $currentMonth . '/' . $currentYear;
    }

    public function recalculate($invoiceId)
    {
        $invoice = $this->getById($invoiceId);

        if (! $invoice) {
            return null;
        }

        // Delete all invoice payments for this invoice
        $invoice->payments()->delete();

        // Calculate new amounts
        $totals = $this->calculateInvoiceAmount($invoice);

        // Update invoice: reset status to CREATE and update amounts
        $this->service->where('id', $invoiceId)->update([
            'status' => Invoice::STATUS_CREATE, // Reset to CREATE status
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
        ]);

        $this->logActivity('Invoice', $invoice, 'Recalculate Amount and Cancel Payments');

        return [
            'invoiceAmount' => $totals['subtotal'],
            'ppnAmount' => $totals['ppn'],
            'total' => $totals['total'],
        ];
    }
}
