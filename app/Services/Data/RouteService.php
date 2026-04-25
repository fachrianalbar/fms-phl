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
        $filtered = Arr::only($request->all(), [
            'price',
            'vendorPrice',
            'personalVendorPrice',
            'customerCode',
            'routeTypeCode',
            'originLocationCode',
            'destinationLocationCode',
            'name',
        ]);

        for ($i = 0; $i < count($request->price); $i++) {
            $price = $this->normalizeDecimal($filtered['price'][$i] ?? 0);
            $vendorPrice = $this->normalizeDecimal($filtered['vendorPrice'][$i] ?? 0);
            $personalVendorPrice = $this->normalizeDecimal($filtered['personalVendorPrice'][$i] ?? 0);

            if ($vendorPrice > $price) {
                throw new \InvalidArgumentException('Vendor price cannot be greater than price');
            }

            if ($personalVendorPrice > $price) {
                throw new \InvalidArgumentException('Personal vendor price cannot be greater than price');
            }

            $data = $this->service->create(
                [
                    'name' => $filtered['name'][$i],
                    'customerCode' => $filtered['customerCode'][$i],
                    'originLocationCode' => $filtered['originLocationCode'][$i],
                    'destinationLocationCode' => $filtered['destinationLocationCode'][$i],
                    'price' => $price,
                    'vendorPrice' => $vendorPrice,
                    'routeTypeCode' => $filtered['routeTypeCode'][$i],
                    'personalVendorPrice' => $personalVendorPrice,
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
        $before = $this->getById($id);
        $this->logActivity($title, $before, 'Before Update');

        $price = $this->normalizeDecimal($request->price);
        $personalVendorPrice = $this->normalizeDecimal($request->personalVendorPrice);

        $existingVendorPrice = $before ? (float) $before->vendorPrice : 0;

        if ($existingVendorPrice > $price) {
            throw new \InvalidArgumentException('Existing vendor price cannot be greater than price. Please update external vendor prices first.');
        }

        if ($personalVendorPrice > $price) {
            throw new \InvalidArgumentException('Personal vendor price cannot be greater than price');
        }

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'customerCode' => $request->customerCode,
            'originLocationCode' => $request->originLocationCode,
            'destinationLocationCode' => $request->destinationLocationCode,
            // 'fleetTypeCode' => $request->fleetTypeCode,
            'price' => $price,
            'routeTypeCode' => $request->routeType,
            'personalVendorPrice' => $personalVendorPrice,
            'description' => $request->description,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }

    private function normalizeDecimal($value): float
    {
        if ($value === null || $value === '') {
            return 0.00;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $normalized = str_replace(' ', '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/\.(?=.*\.)/', '', $normalized);

        return round((float) $normalized, 2);
    }

    public function bulkUpdatePrice($request, $title)
    {
        $ids = $request->ids;
        $type = $request->type; // 'increase' or 'decrease'
        $percentage = (float) $request->percentage;
        $targets = $request->targets ?? []; // array of target columns: 'price', 'vendorPrice', 'personalVendorPrice'

        if (empty($ids) || empty($targets) || $percentage <= 0) {
            throw new \InvalidArgumentException('Invalid parameters for bulk update.');
        }

        $multiplier = $type === 'increase' ? (1 + ($percentage / 100)) : (1 - ($percentage / 100));

        $routes = $this->service->whereIn('id', $ids)->get();

        foreach ($routes as $route) {
            $this->logActivity($title, $route, 'Before Bulk Update Price');

            $updateData = [];

            if (in_array('price', $targets)) {
                $updateData['price'] = round($route->price * $multiplier, 2);
            }
            if (in_array('vendorPrice', $targets)) {
                $updateData['vendorPrice'] = round($route->vendorPrice * $multiplier, 2);
            }
            if (in_array('personalVendorPrice', $targets)) {
                $updateData['personalVendorPrice'] = round($route->personalVendorPrice * $multiplier, 2);
            }

            // Ensure vendor prices don't exceed the new or existing price
            $newPrice = isset($updateData['price']) ? $updateData['price'] : $route->price;
            $newVendorPrice = isset($updateData['vendorPrice']) ? $updateData['vendorPrice'] : $route->vendorPrice;
            $newPersonalVendorPrice = isset($updateData['personalVendorPrice']) ? $updateData['personalVendorPrice'] : $route->personalVendorPrice;

            if ($newVendorPrice > $newPrice || $newPersonalVendorPrice > $newPrice) {
                // Adjust if necessary, or let it throw an exception if strict rules apply. We'll adjust it to the max price for now or skip updating if it violates.
                // It's safer to adjust the vendor prices to the new price limit.
                if ($newVendorPrice > $newPrice) {
                    $updateData['vendorPrice'] = $newPrice;
                }
                if ($newPersonalVendorPrice > $newPrice) {
                    $updateData['personalVendorPrice'] = $newPrice;
                }
            }

            $route->update($updateData);

            $this->logActivity($title, $route->fresh(), 'After Bulk Update Price');
        }
    }
}
