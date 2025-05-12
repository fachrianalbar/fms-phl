<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Unit;
use App\Traits\LogActivity;

class UnitService
{
    use LogActivity;

    protected $service;

    public function __construct(Unit $unit)
    {
        $this->service = $unit;
    }

    public function findAll()
    {
        return $this->service->where('type', null)->get();
    }

    public function findAllInventory()
    {
        return $this->service->where('type', 'Inventory')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $request->all();
        $data["code"] = GenerateCode::generateCode('TE');
        $result = $this->service->create($data);

        $this->logActivity($title, $result, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
