<?php

namespace App\Services\Master;

use App\Models\Master\CostComponentPriceLog;

class CostComponentPriceLogService
{
    protected $service;

    public function __construct(CostComponentPriceLog $model)
    {
        $this->service = $model;
    }

    public function findAll()
    {
        return $this->service->orderBy('created_at', 'desc')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function query()
    {
        return $this->service->orderBy('created_at', 'desc');
    }
}
