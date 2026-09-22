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

    public function findAll($filters = [])
    {
        return $this->findAllQuery($filters)->get();
    }

    public function findAllQuery($filters = [])
    {
        $query = $this->service->with(['position'])->latest();

        if (! empty($filters['positionCode'])) {
            $query->where('positionCode', $filters['positionCode']);
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function findDriver($onlyActive = true)
    {
        $query = $this->service->whereIn('positionCode', ['KP_240823034043', 'FPS250612034049'])->with(['position'])->orderBy('name');

        if ($onlyActive) {
            $query->where('status', 1);
        }

        return $query->get();
    }

    public function toggleStatus($id, $title)
    {
        $employee = $this->getById($id);
        if (! $employee) {
            return null;
        }

        $this->logActivity($title, $employee, 'Before Toggle Status');

        $newStatus = (int) $employee->status === 1 ? 0 : 1;
        $employee->status = $newStatus;
        $employee->save();

        $this->logActivity($title, $employee, 'After Toggle Status: ' . ($newStatus === 1 ? 'Aktif' : 'Nonaktif'));

        return $employee;
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $request->all();
        $data['code'] = GenerateCode::generateCode('TE');
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
