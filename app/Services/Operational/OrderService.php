<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;


class OrderService
{
    use LogActivity;

    protected $service;

    public function __construct(Order $order)
    {
        $this->service = $order;
    }

    public function findAll()
    {
        return $this->service->with([
            // 'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type'
        ])->orderBy('created_at', 'desc')->get();
    }

    public function datatable()
    {
        return $this->service->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type'
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
        $route = Route::where('customerCode', $request->customerCode)
            ->where('originLocationCode', $request->originLocationCode)
            ->where('destinationLocationCode', $request->destinationLocationCode)
            ->where('routeTypeCode', $request->routeTypeCode)
            ->first();

        $data = $this->service->create([
            'code' =>  $request->code,
            'shipmentNumber' => $request->shipmentNumber,
            'orderDate' => $request->orderDate,
            // 'shipmentNumber' => $request->shipmentNumber,
            'orderDate' => $request->orderDate,
            'materialCode' => $request->materialCode,
            'notes' => $request->notes,
            'sto' => $request->sto,
            'salesOrder' => $request->salesOrder,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $route->code,
            'qty' => $request->qty,
            'routeCode' => $route->code,
            'orderTypeCode' => $request->orderTypeCode,
            'customerCode' => $request->customerCode,
            // 'fleetTypeCode' => $request->fleetTypeCode
        ]);

        if (isset($request->nominal)) {
            $filtered = Arr::only($request->all(), ['componentName', 'description', 'nominal']);

            for ($i = 0; $i < count($request->nominal); $i++) {

                $orderCost = OrderCost::create([
                    'code' => GenerateCode::generateCode('TOC', true),
                    'componentType' => $filtered['componentName'][$i],
                    'orderCode' => $request->code,
                    'nominal' => (int)str_replace('.', '', $filtered['nominal'][$i]),
                    // 'type' => $filtered['componentType'][$i],
                    'description' => $filtered['description'][$i]
                ]);

                $this->logActivity('Order Cost', $orderCost, 'Create');
            }
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $data = $this->getById($id);
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $route = Route::where('customerCode', $request->customerCode)
            ->where('originLocationCode', $request->originLocationCode)
            ->where('destinationLocationCode', $request->destinationLocationCode)
            ->where('routeTypeCode', $request->routeTypeCode)
            ->first();

        $this->service->where('id', $id)->update([
            'shipmentNumber' => $request->shipmentNumber,
            'orderDate' => $request->orderDate,
            // 'shipmentNumber' => $request->shipmentNumber,
            'orderDate' => $request->orderDate,
            'materialCode' => $request->materialCode,
            'notes' => $request->notes,
            'sto' => $request->sto,
            'salesOrder' => $request->salesOrder,
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode,
            'routeCode' => $route->code,
            'qty' => $request->qty,
            'routeCode' => $route->code,
            'orderTypeCode' => $request->orderTypeCode,
            'customerCode' => $request->customerCode,
            // 'fleetTypeCode' => $request->fleetTypeCode
        ]);



        if (isset($request->nominal)) {
            $data->cost()->delete();
            $filtered = Arr::only($request->all(), ['componentName', 'description',  'nominal']);

            for ($i = 0; $i < count($request->nominal); $i++) {

                $orderCost = OrderCost::create([
                    'code' => GenerateCode::generateCode('TOC', true),
                    'componentType' => $filtered['componentName'][$i],
                    'orderCode' => $request->code,
                    'nominal' => (int)str_replace('.', '', $filtered['nominal'][$i]),
                    // 'type' => $filtered['componentType'][$i],
                    'description' => $filtered['description'][$i]
                ]);

                $this->logActivity('Order Cost', $orderCost, 'Create');
            }
        }

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
