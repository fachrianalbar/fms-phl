<?php

namespace App\Services\Bank;

use App\Helpers\GenerateCode;
use App\Models\Bank\UserBank;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Carbon\Carbon;

class UserBankService
{
    use LogActivity;

    protected $service;

    public function __construct(UserBank $userBank)
    {
        $this->service = $userBank;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function findCompany()
    {
        return $this->service->where('type', 2)->with(['bank'])->get();
    }

    public function findPerson()
    {
        return $this->service->where('type', 1)->with(['bank'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function getByUser($code)
    {
        return $this->service->where('userCode', $code)->get();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'code' => GenerateCode::generateCode('FUB'),
            'accountName' => $request->accountName,
            'bankCode' => $request->bankCode,
            'type' => $request->type,
            // 'balance' => (int)$request->balance,
            'accountNumber' => $request->accountNumber,
        ]);

        LiveMutation::create([
            'code' => GenerateCode::generateCode('FLM'),
            'userBankCode' => $data->code,
            'debit' => (int) $request->balance,
            'credit' => 0,
            'balance' => (int) $request->balance,
        ]);

        if ((int) $request->balance != 0) {
            $mutation = Mutation::create([
                'code' => GenerateCode::generateCode('FMT'),
                'userBankCode' => $data->code,
                'nominal' => (int) $request->balance,
                'type' => 'In',
                'date' => Carbon::now(),
                'description' => 'New Balance',
                'transactionTypeCode' => 'FTT250306114179',
            ]);

            $this->logActivity('Mutation', $mutation, 'Create');
        }

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'accountName' => $request->accountName,
            'bankCode' => $request->bankCode,
            'type' => $request->type,
            // 'balance' => (int)$request->balance,
            'accountNumber' => $request->accountNumber,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
