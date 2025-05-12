<?php

namespace App\Services\Bank;

use App\Helpers\GenerateCode;
use App\Models\Bank\ConfigBank;
use App\Models\Bank\UserBank;
use App\Models\User;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;


class ConfigBankService
{
    use LogActivity;

    protected $service;
    protected $user;
    protected $userBank;

    public function __construct(ConfigBank $configBank, User $user, UserBank $userBank)
    {
        $this->service = $configBank;
        $this->user = $user;
        $this->userBank = $userBank;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function datatable()
    {
        return $this->user->select('users.code', 'users.username')
            ->join('config_bank', 'config_bank.userCode', 'users.code')
            ->with(['configBank.userBank.bank'])
            ->whereNull('config_bank.deleted_at')
            ->groupBy(['users.code', 'users.username'])
            ->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }


    public function getByUser($code)
    {
        return $this->user->where('code', $code)->with(['configBank.userBank.bank'])->first();
    }


    public function store($request, $title)
    {

        foreach ($request->userBankCode as $item) {
            $data = $this->service->create([
                'code' => GenerateCode::generateCode('FCB'),
                'userCode' => $request->userCode,
                'userBankCode' => $item,
            ]);

            $this->logActivity($title, $data, 'Create');
        }
    }

    public function update($request, $id, $title)
    {
        // $this->logActivity($title, $this->getById($id), 'Before Update');

        if (isset($request->userBankCode)) {
            foreach ($request->userBankCode as $item) {
                $data = $this->service->create([
                    'userCode' => $request->userCode,
                    'userBankCode' => $item,
                ]);

                $this->logActivity($title, $data, 'Update');
            }
        }
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }



    public function destroyByUser($code, $title)
    {
        $this->logActivity($title, $this->getByUser($code), 'Delete All');

        $this->service->where('userCode', $code)->delete();
    }

    public function listUserBank()
    {
        $userBankCode = $this->service->pluck('userBankCode')->toArray();

        return $this->userBank->whereNotIn('code', $userBankCode)->with(['bank'])->get();
    }
}
