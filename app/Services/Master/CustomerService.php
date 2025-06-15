<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Customer;
use App\Models\Master\CustomerDetail;
use App\Models\Operational\CustomerDetailOrder;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;


class CustomerService
{
    use LogActivity;

    protected $service;
    protected $customerDetail;
    protected $customerDetailOrder;

    public function __construct(Customer $customer, CustomerDetail $customerDetail, CustomerDetailOrder $customerDetailOrder)
    {
        $this->service = $customer;
        $this->customerDetail = $customerDetail;
        $this->customerDetailOrder = $customerDetailOrder;
    }

    public function findAll()
    {
        return $this->service->with(['company'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details'])->first();
    }

    public function getByCode($code)
    {
        return $this->service->where('code', $code)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'code' => GenerateCode::generateCode('FC'),
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
            'companyCode' => $request->companyCode,
            'due_date_duration' => $request->due_date_duration,
            'type' => $request->type,
            'isDo' => $request->isDo
            // 'telegramUsername' => $request->telegramUsername
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
            'companyCode' => $request->companyCode,
            'due_date_duration' => $request->due_date_duration,
            'type' => $request->type,
            'isDo' => $request->isDo
            // 'telegramUsername' => $request->telegramUsername
        ]);

        $data = $this->getById($id);


        if (isset($request->nameDetail)) {
            $filtered = Arr::only($request->all(), ['nameDetail']);

            $this->customerDetail->where('customerCode', $data->code)->delete();

            for ($i = 0; $i < count($request->nameDetail); $i++) {
                $dataDetail = $this->customerDetail->create([
                    'code' => GenerateCode::generateCode('FCD', true),
                    'name' => $filtered['nameDetail'][$i],
                    'customerCode' => $data->code
                ]);
                $this->logActivity('Customer Detail', $dataDetail, 'Create');
            }
        }
        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }

    public function deleteCustomerDetail($id)
    {
        $dataDetail = $this->customerDetail->where('id', $id)->with(['customerDetailOrders'])->first();

        $this->logActivity('Customer Detail', $dataDetail, 'Delete');

        $this->customerDetailOrder->where('customerDetailCode', $dataDetail->code)->delete();

        $dataDetail->delete();
    }

    public function customerDetail($code)
    {
        return $this->customerDetail->where('customerCode', $code)->get();
    }
}
