<?php

namespace App\Services\Data;

use App\Helpers\GenerateCode;
use App\Models\Data\RouteDetail;
use App\Traits\LogActivity;

class RouteDetailService
{
    use LogActivity;

    protected $service;

    public function __construct(RouteDetail $routeDetail)
    {
        $this->service = $routeDetail;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with(['route'])->first();
    }

    public function store($request, $title)
    {
        $routeData = json_decode($request->routeData);

        // Loop untuk memastikan kode unik
        do {
            $code = GenerateCode::generateCode('TRD');
            $exists = $this->service->where('code', $code)->first();
        } while ($exists);

        // Simpan data dengan kode unik
        $data = $this->service->create([
            'type' => $request->componentType,
            'amount' => (int) $request->amount,
            'percentage' => $request->percentage,
            'componentCode' => $request->componentCode,
            'routeCode' => $routeData->code,
            'code' => $code,
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
