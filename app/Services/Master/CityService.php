<?php

namespace App\Services\Master;

use App\Models\Master\City;
use App\Models\Master\Location;

class CityService
{

    protected $service;

    public function __construct(City $city)
    {
        $this->service = $city;
    }

    public function findAll()
    {
        return $this->service->orderBy('name', 'asc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByProvince($id)
    {
        return $this->service->where('province_id', $id)->get();
    }
}
