<?php

namespace App\Services;

use App\Models\Mutation;
use App\Traits\LogActivity;

class MutationService
{
    use LogActivity;

    protected $service;

    public function __construct(Mutation $mutation)
    {
        $this->service = $mutation;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByUserBankCode($userBankCode)
    {
        return $this->service->where('userBankCode', $userBankCode)->latest()->get();
    }
}
