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

    /**
     * Data untuk datatable user-bank: eager load relasi + filter status pill,
     * dipetakan menjadi array bersih (hanya kolom yang dipakai tabel).
     * Status: all | person | company | internal | external.
     */
    public function findAllFiltered(string $status = 'all')
    {
        $query = $this->service->with(['bank', 'liveMutation']);

        if ($status === 'person') {
            $query->where('type', 1);
        } elseif ($status === 'company') {
            $query->where('type', 2);
        } elseif ($status === 'internal') {
            $query->where('rekening_type', 'internal');
        } elseif ($status === 'external') {
            $query->where('rekening_type', 'external');
        }

        return $query->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'bank_name' => $row->bank->name ?? '',
                'accountNumber' => $row->accountNumber,
                'accountName' => $row->accountName,
                'type' => $row->type,
                'rekening_type' => $row->rekening_type,
                'bankCode' => $row->bankCode,
                'balance' => (float) ($row->liveMutation->balance ?? 0),
            ];
        });
    }

    /**
     * Statistik agregat untuk KPI cards halaman index.
     */
    public function getStats()
    {
        $banks = $this->service->get();

        return [
            'totalCount' => $banks->count(),
            'personCount' => $banks->where('type', 1)->count(),
            'companyCount' => $banks->where('type', 2)->count(),
            'internalCount' => $banks->filter(function ($bank) {
                return strtolower($bank->rekening_type ?? '') === 'internal';
            })->count(),
            'externalCount' => $banks->filter(function ($bank) {
                return strtolower($bank->rekening_type ?? '') !== 'internal';
            })->count(),
            'totalBalance' => (int) $this->service->with('liveMutation')->get()
                ->sum(function ($bank) {
                    return (int) ($bank->liveMutation->balance ?? 0);
                }),
        ];
    }

    public function findCompany()
    {
        return $this->service->where('type', 2)->with(['bank', 'liveMutation'])->get();
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
            'rekening_type' => $request->rekening_type ?? 'external',
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
            'rekening_type' => $request->rekening_type ?? $this->getById($id)->rekening_type ?? 'external',
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
