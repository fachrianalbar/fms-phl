<?php

namespace App\Services\Data;

use App\Helpers\GenerateCode;
use App\Models\Data\DropLocation;
use App\Traits\LogActivity;

class DropLocationService
{
    use LogActivity;

    protected $service;

    public function __construct(DropLocation $dropLocation)
    {
        $this->service = $dropLocation;
    }

    public function findAll()
    {
        return $this->service->with(['location'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'address' => $request->address,
            'description' => $request->description,
            'locationCode' => $request->locationCode,
            'code' => GenerateCode::generateCode('TDL'),
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'address' => $request->address,
            'description' => $request->description,
            'locationCode' => $request->locationCode,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
