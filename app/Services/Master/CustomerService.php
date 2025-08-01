<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Customer;
use App\Models\Master\CustomerDetail;
use App\Models\Master\CustomerPic;
use App\Models\Operational\CustomerDetailOrder;
use App\Traits\LogActivity;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class CustomerService
{
    use LogActivity;

    protected $service;
    protected $customerDetail;
    protected $customerDetailOrder;
    protected $customerPic;

    public function __construct(Customer $customer, CustomerDetail $customerDetail, CustomerDetailOrder $customerDetailOrder, CustomerPic $customerPic)
    {
        $this->service = $customer;
        $this->customerDetail = $customerDetail;
        $this->customerDetailOrder = $customerDetailOrder;
        $this->customerPic = $customerPic;
    }

    public function findAll()
    {
        return $this->service->with(['company'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['details', 'pic'])->first();
    }

    public function getByCode($code)
    {
        return $this->service->where('code', $code)->with(['pic'])->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'code' => $request->code,
            // 'picName' => $request->picName,
            // 'nickname' => $request->nickname,
            'email' => $request->email,
            // 'phone' => $request->phone,
            'officeAddress' => $request->officeAddress,
            'billingAddress' => $request->billingAddress,
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

        if (isset($request->picName)) {
            $this->storeCustomerPic($request);
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'code' => $request->code,
            'name' => $request->name,
            // 'picName' => $request->picName,
            // 'nickname' => $request->nickname,
            'email' => $request->email,
            // 'phone' => $request->phone,
            'officeAddress' => $request->officeAddress,
            'billingAddress' => $request->billingAddress,
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

        if (isset($request->picName)) {
            $data->pic()->delete();
            $this->storeCustomerPic($request);
        }


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

        $data = $this->getById($id);

        $this->service->where('id', $id)->update([
            'code' => $data->code . '-del-' . Str::random(3)
        ]);

        $this->service->where('id', $id)->delete();
    }

    public function deleteCustomerDetail($id)
    {
        $dataDetail = $this->customerDetail->where('id', $id)->with(['customerDetailOrders'])->first();

        $this->logActivity('Customer Detail', $dataDetail, 'Delete');

        $this->customerDetailOrder->where('customerDetailCode', $dataDetail->code)->delete();

        $dataDetail->delete();
    }

    public function deleteCustomerPic($id)
    {
        $dataPic = $this->customerPic->where('id', $id)->first();

        $this->logActivity('Customer Pic', $dataPic, 'Delete');

        $dataPic->delete();
    }

    public function customerDetail($customerId)
    {
        $customer = $this->service->where('id', $customerId)->first();
        return $this->customerDetail->where('customerCode', $customer->code)->get();
    }

    public function customerCompanyFormat($code)
    {
        return $this->service->where('code', $code)->with(['company'])->first();
    }

    public function storeCustomerPic($request)
    {
        $filtered = Arr::only($request->all(), ['picName', 'phone']);

        for ($i = 0; $i < count($request->picName); $i++) {

            $customerPic = $this->customerPic->create([
                'code' => GenerateCode::generateCode('FCP', true),
                'picName' => $filtered['picName'][$i],
                'phone' => $filtered['phone'][$i],
                'customerCode' =>  $request->code
            ]);

            $this->logActivity('Customer Pic', $customerPic, 'Create');
        }
    }
}
