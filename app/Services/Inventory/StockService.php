<?php

namespace App\Services\Inventory;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Master\Position;
use App\Traits\LogActivity;

class StockService
{
    use LogActivity;

    protected $service;
    protected $item;

    public function __construct(Stock $stock, Item $item)
    {
        $this->service = $stock;
        $this->item = $item;
    }

    public function findAll()
    {
        return $this->service->with(['item'])->get();
    }

    public function datatable()
    {
        return $this->item->with(['stock']);
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }
}
