<?php

namespace App\Services\Data;

use App\Helpers\GenerateCode;
use App\Models\Data\Driver;
use App\Models\Data\FleetDriver;
use App\Traits\LogActivity;

class FleetDriverService
{
    use LogActivity;

    protected $service;

    public function __construct(FleetDriver $fleetDriver)
    {
        $this->service = $fleetDriver;
    }

    public function findAll()
    {
        return $this->service->with(['fleet', 'fleetType'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            // 'driverCode' => $request->driverCode,
            'fleetTypeCode' => $request->fleetTypeCode,
            'fleetCode' => $request->fleetCode,
            'vehicleRegistrationNumber' => $request->vehicleRegistrationNumber,
            'vehicleRegistrationNumberExpDate' => $request->vehicleRegistrationNumberExpDate,
            'kir' => $request->kir,
            'kirExpDate' => $request->kirExpDate,
            'code' => GenerateCode::generateCode('TFD')
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            // 'driverCode' => $request->driverCode,
            'fleetTypeCode' => $request->fleetTypeCode,
            'fleetCode' => $request->fleetCode,
            'vehicleRegistrationNumber' => $request->vehicleRegistrationNumber,
            'vehicleRegistrationNumberExpDate' => $request->vehicleRegistrationNumberExpDate,
            'kir' => $request->kir,
            'kirExpDate' => $request->kirExpDate,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
