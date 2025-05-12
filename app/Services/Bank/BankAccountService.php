<?php

namespace App\Services\Bank;

use App\Helpers\GenerateCode;
use App\Models\Bank\BankAccount;
use App\Traits\LogActivity;

class BankAccountService
{
    use LogActivity;

    protected $service;

    public function __construct(BankAccount $bankAccount)
    {
        $this->service = $bankAccount;
    }

    public function findAll()
    {
        return $this->service->orderBy('name')->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->where('bankCode', $request->bankCode)->first();

        if ($data) {
            return false;
        }

        $result = $this->service->create([
            'code' => GenerateCode::generateCode('FBA'),
            'name' => $request->name,
            'bankCode' => $request->bankCode
        ]);

        $this->logActivity($title, $result, 'Create');

        return true;
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'bankCode' => $request->bankCode
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
