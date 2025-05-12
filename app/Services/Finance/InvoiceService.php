<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Carbon\Carbon;

class InvoiceService
{
    use LogActivity;

    protected $service;
    protected $order;
    protected $invoiceDetail;

    public function __construct(Invoice $invoice, Order $order, InvoiceDetail $invoiceDetail)
    {
        $this->service = $invoice;
        $this->order = $order;
        $this->invoiceDetail = $invoiceDetail;
    }

    public function findAll()
    {
        return $this->service->with(['details'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details', 'customer', 'payments'])->first();
    }

    public function getOrder()
    {
        return $this->order->where('status', 4)->with([
            // 'fleetDriver.fleet',
            'fleet',
            'fleet.type',
            // 'fleetDriver.employee',
            'driver',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'route.originLocation',
            'orderType',
            'route.routeDetail',
        ])->orderBy('created_at', 'desc');
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
            'notes' => $request->notes
        ]);

        if (isset($request->order)) {
            foreach ($selectedOrders as $item) {
                $detail = $this->invoiceDetail->create([
                    'code' => GenerateCode::generateCode('INVD', true),
                    'invoiceCode' => $data->code,
                    'orderCode' => $item
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5
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
            'notes' => $request->notes
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        foreach ($data->details as $item) {
            $this->order->where('code', $item->orderCode)->update([
                'status' => 4
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
            'route.routeDetail'
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
                    'orderCode' => $item
                ]);

                $this->order->where('code', $item)->update([
                    'status' => 5
                ]);

                $this->logActivity('Invoice Detail', $detail, 'Create');
            }
        }
    }

    public function destroyInvoiceDetail($id)
    {
        $order = $this->order->where('id', $id)->first();

        $this->order->where('id', $id)->update([
            'status' => 4
        ]);

        $data = $this->invoiceDetail->where('orderCode', $order->code)->first();

        $this->logActivity('Invoice Detail', $data, 'Delete');

        $this->invoiceDetail->where('orderCode', $order->code)->delete();
    }
}
