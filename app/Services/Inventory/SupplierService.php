<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Supplier;
use App\Services\UniqueCodeService;
use App\Traits\LogActivity;

class SupplierService
{
    use LogActivity;

    protected $service;

    public function __construct(Supplier $supplier, private UniqueCodeService $uniqueCode)
    {
        $this->service = $supplier;
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
        $data = $request->all();
        $code = $this->uniqueCode->resolve(
            model: Supplier::class,
            field: 'code',
            requestedCode: $request->input('code'),
        );
        $data['code'] = $code->resolvedCode;

        $result = $this->service->create($data);

        $this->logActivity($title, $result, 'Create');

        return $code;
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $data = $request->all();
        unset($data['_token']);
        unset($data['_method']);
        $code = $this->uniqueCode->resolve(
            model: Supplier::class,
            field: 'code',
            requestedCode: $request->input('code'),
            ignoreId: $id,
        );
        $data['code'] = $code->resolvedCode;

        $this->service->where('id', $id)->update($data);

        $this->logActivity($title, $this->getById($id), 'After Update');

        return $code;
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
