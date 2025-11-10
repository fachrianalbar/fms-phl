<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\TransactionType;
use App\Traits\LogActivity;

class TransactionTypeService
{
    use LogActivity;

    protected $service;

    public function __construct(TransactionType $transactionType)
    {
        $this->service = $transactionType;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByUser($code)
    {
        return $this->service->where('userCode', $code)->get();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'code' => GenerateCode::generateCode('FTT'),
            'name' => $request->name,
        ]);

        $this->logActivity($title, $data, 'Create');
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
