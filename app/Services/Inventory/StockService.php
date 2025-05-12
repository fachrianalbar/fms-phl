<?php

namespace App\Services\Inventory;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Stock;
use App\Models\Master\Position;
use App\Traits\LogActivity;

class StockService
{
    use LogActivity;

    protected $service;

    public function __construct(Stock $stock)
    {
        $this->service = $stock;
    }

    public function findAll()
    {
        return $this->service->with(['item'])->get();
    }

    public function datatable()
    {
        return $this->service->with(['item']);
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }
}
