<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Employee;
use App\Traits\LogActivity;

class EmployeeService
{
    use LogActivity;

    protected $service;

    public function __construct(Employee $employee)
    {
        $this->service = $employee;
    }

    public function findAll()
    {
        return $this->service->with(['position'])->get();
    }

    public function findDriver()
    {
        return $this->service->where('positionCode', 'KP_240823034043')->with(['position'])->get();
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
