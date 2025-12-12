<?php

namespace App\Services\Data;

use App\Helpers\GenerateCode;
use App\Models\Data\Route;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;

class RouteService
{
    use LogActivity;

    protected $service;

    public function __construct(Route $route)
    {
        $this->service = $route;
    }

    public function findAll()
    {
        return $this->service->with(['customer', 'originLocation', 'destinationLocation', 'fleetType', 'routeDetail', 'routeType'])->get();
    }

    public function datatable()
    {
        return $this->service->with(['customer', 'originLocation', 'destinationLocation', 'fleetType', 'routeDetail', 'routeType'])
            ->leftJoin('customer', 'customer.code', 'route.customerCode')
            ->leftJoin('location as origin_location', 'origin_location.code', 'route.originLocationCode')
            ->leftJoin('location as destination_location', 'destination_location.code', 'route.destinationLocationCode')
            ->leftJoin('route_type', 'route_type.code', 'route.routeTypeCode')
            ->leftJoin('fleet_type', 'fleet_type.code', 'route.fleetTypeCode')
            ->select('route.*');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['routeDetail', 'routeDetail.costComponent'])->first();
    }

    public function store($request, $title)
    {
        $filtered = Arr::only($request->all(), ['price', 'customerCode', 'routeTypeCode', 'originLocationCode', 'destinationLocationCode', 'name']);

        for ($i = 0; $i < count($request->price); $i++) {

            $data = $this->service->create(
                [
                    'name' => $filtered['name'][$i],
                    'customerCode' => $filtered['customerCode'][$i],
                    'originLocationCode' => $filtered['originLocationCode'][$i],
                    'destinationLocationCode' => $filtered['destinationLocationCode'][$i],
                    'price' => (int) $filtered['price'][$i],
                    // 'vendorPrice' => (int)$filtered['vendorPrice'][$i],
                    'routeTypeCode' => $filtered['routeTypeCode'][$i],
                    'code' => GenerateCode::generateCode('TR', true),
                ]
            );
            $this->logActivity($title, $data, 'Create');
        }

        return $data;
    }

    public function update($request, $id, $title)
    {
        // dd($request->all());
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'customerCode' => $request->customerCode,
            'originLocationCode' => $request->originLocationCode,
            'destinationLocationCode' => $request->destinationLocationCode,
            // 'fleetTypeCode' => $request->fleetTypeCode,
            'price' => (int) $request->price,
            'vendorPrice' => (int) $request->vendorPrice,
            'routeTypeCode' => $request->routeType,
            'personalVendorPrice' => (int) $request->personalVendorPrice,
            'description' => $request->description,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
