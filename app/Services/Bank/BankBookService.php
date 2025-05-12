<?php

namespace App\Services\Bank;

use App\Models\LiveMutation;
use App\Traits\LogActivity;

class BankBookService
{
    use LogActivity;

    protected $service;

    public function __construct(LiveMutation $saldoUser)
    {
        $this->service = $saldoUser;
    }

    public function findAll()
    {
        return $this->service->with(['userBank'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function findByUserBankCode($userBankCode)
    {
        return $this->service->where('userBankCode', $userBankCode)->with(['userBank'])->first();
    }
}
