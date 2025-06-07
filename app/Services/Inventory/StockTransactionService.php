<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Item;
use App\Models\StockTransaction;
use App\Traits\LogActivity;

class StockTransactionService
{
    use LogActivity;

    protected $service;
    protected $item;

    public function __construct(StockTransaction $stockTransaction, Item $item)
    {
        $this->service = $stockTransaction;
        $this->item = $item;
    }

    public function findAll()
    {

        $item = $this->service->pluck('itemCode')->unique()->toArray();

        return $this->item->whereIn('code', $item)->get();
    }


    public function datatable()
    {
        return $this->service->with(['item', 'purchase', 'maintenance'])->orderBy('created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }
}
