<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Operational\DownPaymentDetail;
use App\Traits\LogActivity;

class DownPaymentDetailService
{
    use LogActivity;

    protected $service;

    public function __construct(DownPaymentDetail $dp)
    {
        $this->service = $dp;
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
            'dpCode' => $request->dpCode,
            'date' => $request->date,
            'time' => $request->time,
            'price' => $request->price,
            'note' => $request->note,
            'code' => GenerateCode::generateCode('TDPD'),
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'date' => $request->date,
            'time' => $request->time,
            'price' => $request->price,
            'note' => $request->note,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
