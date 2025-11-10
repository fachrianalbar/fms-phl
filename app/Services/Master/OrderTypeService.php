<?php

namespace App\Services\Master;

use App\Models\Master\OrderType;
use App\Traits\LogActivity;

class OrderTypeService
{
    use LogActivity;

    protected $service;

    public function __construct(OrderType $orderType)
    {
        $this->service = $orderType;
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
