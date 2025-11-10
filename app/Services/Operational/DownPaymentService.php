<?php

namespace App\Services\Operational;

use App\Models\Master\Employee;
use App\Models\Operational\DownPayment;
use App\Models\Operational\DownPaymentDetail;
use App\Traits\LogActivity;

class DownPaymentService
{
    use LogActivity;

    protected $service;

    protected $employee;

    protected $detail;

    public function __construct(DownPayment $downPayment, Employee $employee, DownPaymentDetail $detail)
    {
        $this->service = $downPayment;
        $this->employee = $employee;
        $this->detail = $detail;
    }

    public function datatable()
    {
        return $this->employee->with(['downPayment'])->get();
    }

    public function findAll()
    {
        return $this->service->with(['driver', 'details'])->get();
    }

    public function getById($id)
    {
        // $employee = $this->employee->where('id', $id)->first();

        // if ($employee) {
        //     return $employee;
        // }

        return $this->service->where('id', $id)->first();
    }

    public function detail($id)
    {
        return $this->detail->where('dpCode', $id)->get();
    }

    public function getByDriver($code)
    {
        return $this->service->where('driverCode', $code)->with(['driver', 'picUser'])->get();
    }

    public function store($request, $title)
    {
        $data = $this->service->create($request->all());

        $this->logActivity($title, $data, 'Create');
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
