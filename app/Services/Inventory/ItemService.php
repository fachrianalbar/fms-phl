<?php

namespace App\Services\Inventory;

use App\Helpers\GenerateCode;
use App\Models\Inventory\Item;
use App\Traits\LogActivity;

class ItemService
{
    use LogActivity;

    protected $service;

    public function __construct(Item $item)
    {
        $this->service = $item;
    }

    public function findAll()
    {
        return $this->service->with(['unit', 'category', 'location', 'warehouse', 'supplier'])->orderBy('code', 'asc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $request->all();
        $result = $this->service->create($data);

        $this->logActivity($title, $result, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $data = $request->all();
        unset($data['_token']);
        unset($data['_method']);
        $this->service->where('id', $id)->update($data);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
