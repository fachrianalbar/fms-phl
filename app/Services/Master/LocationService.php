<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Helpers\GetAddress;
use App\Models\Master\Location;
use App\Traits\LogActivity;

class LocationService
{
    use LogActivity;

    protected $service;
    protected $provinceSvc;
    protected $citySvc;
    protected $districtSvc;

    public function __construct(Location $location, ProvinceService $provinceSvc, CityService $citySvc, DistrictService $districtSvc, MenuService $menuSvc)
    {
        $this->service = $location;
        $this->citySvc = $citySvc;
        $this->districtSvc = $districtSvc;
        $this->provinceSvc = $provinceSvc;
    }

    public function findAll()
    {
        return $this->service->with(['province', 'city', 'district', 'customer'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByCustomer($customer)
    {
        return $this->service->where('customerCode', $customer)->get();
    }

    public function store($request, $title)
    {
        $address = GetAddress::getAddress($request->latitude, $request->longitude);

        $data = $this->service->create([
            'name' => $request->name,
            'code' => GenerateCode::generateCode('TL'),
            // 'customerCode' => $request->customerCode,
            'provinceId' => $request->provinceId,
            'cityId' => $request->cityId,
            'districtId' => $request->districtId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' =>  $address
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $address = GetAddress::getAddress($request->latitude, $request->longitude);

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            // 'customerCode' => $request->customerCode,
            'provinceId' => $request->provinceId,
            'cityId' => $request->cityId,
            'districtId' => $request->districtId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $address
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
