<?php

namespace App\Services\Master;

use App\Models\Master\DueDate;
use App\Traits\LogActivity;

class DueDateService
{
    use LogActivity;

    protected $service;

    public function __construct(DueDate $dueDate)
    {
        $this->service = $dueDate;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'days' => $request->days,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }
}
