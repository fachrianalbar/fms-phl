<?php

namespace App\Services\Master;

use App\Models\Master\RouteType;
use App\Traits\LogActivity;

class RouteTypeService
{
    use LogActivity;

    protected $service;

    public function __construct(RouteType $routeType)
    {
        $this->service = $routeType;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }
}
