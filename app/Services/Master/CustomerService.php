<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Customer;
use App\Traits\LogActivity;

class CustomerService
{
    use LogActivity;

    protected $service;

    public function __construct(Customer $customer)
    {
        $this->service = $customer;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByCode($code)
    {
        return $this->service->where('code', $code)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'code' => GenerateCode::generateCode('TC'),
            'picName' => $request->picName,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'npwp' => $request->npwp,
            'accountNumber' => $request->accountNumber,
            'ppn' => $request->ppn,
            'pph' => $request->pph,
            'telegramUsername' => $request->telegramUsername
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'picName' => $request->picName,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'npwp' => $request->npwp,
            'accountNumber' => $request->accountNumber,
            'ppn' => $request->ppn,
            'pph' => $request->pph,
            'telegramUsername' => $request->telegramUsername
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
