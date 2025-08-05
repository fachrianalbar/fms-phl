<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Operational\CustomerDetailOrder;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;



class OrderService
{
    use LogActivity;

    protected $service;
    protected $customerDetailOrder;
    protected $orderCost;
    protected $route;
    protected $customer;
    protected $fleet;

    public function __construct(Order $order, CustomerDetailOrder $customerDetailOrder, OrderCost $orderCost, Route $route, Customer $customer, Fleet $fleet)
    {
        $this->service = $order;
        $this->customerDetailOrder = $customerDetailOrder;
        $this->orderCost = $orderCost;
        $this->route = $route;
        $this->customer = $customer;
        $this->fleet = $fleet;
    }

    public function findAll()
    {
        return $this->service->with([
            'driver',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'unit'
        ])->orderBy('created_at', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'fleetDriver.fleet',
            'driver',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'unit'
        ])->orderBy('created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function finishOrder($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 3
        ]);
    }

    public function store($request, $title)
    {
        $data = $this->service->create(
            array_merge(['code' => $request->code, 'shipmentNumber' => $request->shipmentNumber], $this->buildOrderData($request))
        );

        if (isset($request->nominal)) {
            $this->storeOrderCost($request);
        }

        if (isset($request->customerDetailCode)) {
            $this->storeCustomerDetailOrder($request);
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $data = $this->getById($id);
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update(
            $this->buildOrderData($request, true)
        );

        if (isset($request->nominal)) {
            $data->cost()->delete();
            $this->storeOrderCost($request);
        }

        if (isset($request->customerDetailCode)) {
            $this->customerDetailOrder->where('orderCode', $data->code)->delete();
            $this->storeCustomerDetailOrder($request);
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        $this->customerDetailOrder->where('orderCode', $data->code)->delete();

        $this->orderCost->where('orderCode', $data->code)->delete();

        $this->service->where('id', $id)->update([
            'code' => $data->code . '-del-' . Str::random(3),
            'shipmentNumber' => $data->shipmentNumber . '-del-' . Str::random(3)
        ]);

        $this->service->where('id', $id)->delete();
    }

    public function storeOrderTax($selectedOrders)
    {
        $this->service->whereIn('code', $selectedOrders)->update(['is_order_tax' => 1]);
    }

    public function getCustomerDetailOrder($orderCode)
    {
        return $this->customerDetailOrder->where('orderCode', $orderCode)->with(['customerDetail'])->get();
    }

    private function storeOrderCost($request)
    {
        $filtered = Arr::only($request->all(), ['componentName', 'description', 'nominal']);

        for ($i = 0; $i < count($request->nominal); $i++) {

            $orderCost = $this->orderCost->create([
                'code' => GenerateCode::generateCode('TOC', true),
                'componentType' => $filtered['componentName'][$i],
                'orderCode' => $request->code,
                'nominal' => (int)str_replace('.', '', $filtered['nominal'][$i]),
                'description' => $filtered['description'][$i]
            ]);

            $this->logActivity('Order Cost', $orderCost, 'Create');
        }
    }

    private function storeCustomerDetailOrder($request)
    {
        $filtered = Arr::only($request->all(), ['customerDetailCode', 'value']);

        for ($i = 0; $i < count($request->customerDetailCode); $i++) {

            $customerDetailOrder = $this->customerDetailOrder->create([
                'code' => GenerateCode::generateCode('FCDO', true),
                'customerDetailCode' => $filtered['customerDetailCode'][$i],
                'value' => $filtered['value'][$i],
                'orderCode' =>  $request->code
            ]);

            $this->logActivity('Customer Detail Order', $customerDetailOrder, 'Create');
        }
    }

    private function buildOrderData($request, $isUpdate = false)
    {
        $route = $this->route->where('code', $request->routeData)
            ->first();

        return [
            'orderDate' => $request->orderDate,
            'materialCode' => $request->materialCode,
            'notes' => $request->notes,
            'sto' => $request->sto,
            'salesOrder' => $request->salesOrder,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $request->routeData,
            'qty' => $request->qty,
            'orderTypeCode' => $request->orderTypeCode,
            'routeAmount' => $isUpdate ? (int)$request->routeAmount : $route->price,
            'customerCode' => $request->customerCode,
            'unitCode' => $request->unitCode,
            'materialQty' => $request->materialQty
        ];
    }

    public function shipmentFormat($id)
    {
        $customer = $this->customer->where('id', $id)->with(['company'])->first();

        // Ambil shipmentNumber terakhir milik customer yang bersangkutan di tahun berjalan
        $lastShipment = $this->service
            ->where('customerCode', $customer->code)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('created_at')
            ->first();

        // Default increment = 1 jika belum ada shipment sebelumnya
        $lastNumber = 0;

        if ($lastShipment && preg_match('/\/(\d{5})\//', $lastShipment->shipmentNumber, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $increment = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        return $customer->company->format . '/' . $customer->code . '/' . $increment . '/' . now()->year;
    }


    public function getFleet($fleet = null)
    {
        $fleetArr = $this->service->where('status', 0)->pluck('fleetCode')->toArray();

        if ($fleet) {
            $fleetArr = $this->service->where('status', 0)->where('fleetCode', '!=', $fleet)->pluck('fleetCode')->toArray();
        }

        return $this->fleet->whereNotIn('code', $fleetArr)->get();
    }
}
