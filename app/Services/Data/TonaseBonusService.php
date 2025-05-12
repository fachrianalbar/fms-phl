<?php

namespace App\Services\Data;

use App\Helpers\GenerateCode;
use App\Models\Data\TonaseBonus;
use App\Traits\LogActivity;

class TonaseBonusService
{
    use LogActivity;

    protected $service;

    public function __construct(TonaseBonus $tonaseBonus)
    {
        $this->service = $tonaseBonus;
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
            'min' => $request->min,
            'max' => $request->max,
            'value' => (int)$request->value,
            'code' => GenerateCode::generateCode('TTB')
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'min' => $request->min,
            'max' => $request->max,
            'value' => (int)$request->value,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
