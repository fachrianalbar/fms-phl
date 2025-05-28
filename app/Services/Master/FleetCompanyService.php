<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\FleetCompany;
use App\Traits\LogActivity;

class FleetCompanyService
{
    use LogActivity;

    protected $service;

    public function __construct(FleetCompany $fleetCompany)
    {
        $this->service = $fleetCompany;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'type' => $request->type,
            'code' => GenerateCode::generateCode('FFC')
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
