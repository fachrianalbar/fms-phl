<?php

namespace App\Services\Master;

use App\Models\Master\Province;
use App\Traits\LogActivity;

class ProvinceService
{
    use LogActivity;

    protected $service;

    public function __construct(Province $province)
    {
        $this->service = $province;
    }

    public function findAll()
    {
        return $this->service->orderBy('name', 'asc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }
}
