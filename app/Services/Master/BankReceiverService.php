<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\BankReceiver;
use App\Traits\LogActivity;

class BankReceiverService
{
    use LogActivity;

    protected $service;

    public function __construct(BankReceiver $bankReceiver)
    {
        $this->service = $bankReceiver;
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
            'code' => GenerateCode::generateCode('TBR'),
            'bankName' => $request->bankName,
            'accountNumber' => $request->accountNumber,
            'userCode' => $request->userCode,
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'bankName' => $request->bankName,
            'accountNumber' => $request->accountNumber,
            'userCode' => $request->userCode,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
