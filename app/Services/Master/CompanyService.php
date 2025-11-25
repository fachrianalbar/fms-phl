<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
use App\Models\Master\Company;
use App\Traits\LogActivity;

class CompanyService
{
    use LogActivity;

    protected $service;

    public function __construct(Company $company)
    {
        $this->service = $company;
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
            'code' => GenerateCode::generateCode('FCMP'),
            'name' => $request->name,
            'format' => $request->format,
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            // 'format' => $request->format,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
