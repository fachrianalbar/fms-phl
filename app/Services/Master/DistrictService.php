<?php

namespace App\Services\Master;

use App\Models\Master\District;

class DistrictService
{

    protected $service;

    public function __construct(District $district)
    {
        $this->service = $district;
    }

    public function findAll()
    {
        return $this->service->orderBy('name', 'asc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByCity($id)
    {
        return $this->service->where('city_id', $id)->get();
    }
}
