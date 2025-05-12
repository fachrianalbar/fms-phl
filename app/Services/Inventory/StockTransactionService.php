<?php

namespace App\Services\Inventory;

use App\Models\StockTransaction;
use App\Traits\LogActivity;

class StockTransactionService
{
    use LogActivity;

    protected $service;

    public function __construct(StockTransaction $stockTransaction)
    {
        $this->service = $stockTransaction;
    }

    public function findAll()
    {
        return $this->service->with(['item', 'purchase', 'maintenance'])->orderBy('created_at', 'desc')->get();
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
