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
        return $this->service->with(['details'])->get();
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
        $data = $this->service->create([
            'code' => GenerateCode::generateCode('INV'),
            'customerCode' => $request->customerCode,
            'invoiceNumber' => $request->invoiceNumber,
            'receiptNumber' => $request->receiptNumber,
            'poNumber' => $request->poNumber,
            'invoiceDate' => $request->invoiceDate,
            'overdueDate' => Carbon::parse($request->invoiceDate)->addDays(2)->toDateString(),
            'notes' => $request->notes,
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
    }

    public function invoiceNumberFormat($id)
    {
        $customer = $this->customer->where('id', $id)->first();

        // Ambil invoiceNumber terakhir milik customer yang bersangkutan di tahun berjalan
        $lastInvoice = $this->service
            ->where('customerCode', $customer->code)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->first();

        // Default increment = 1 jika belum ada invoice sebelumnya
        $lastNumber = 0;

        if ($lastInvoice && preg_match('/INV\/'.preg_quote($customer->code, '/').'\/(\d{5})\//', $lastInvoice->invoiceNumber, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $increment = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        return 'INV/'.$customer->code.'/'.$increment.'/'.now()->year;
    }
}
