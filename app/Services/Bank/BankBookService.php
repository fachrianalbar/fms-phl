<?php

namespace App\Services\Bank;

use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\DB;

class BankBookService
{
    use LogActivity;

    protected $service;

    public function __construct(LiveMutation $saldoUser)
    {
        $this->service = $saldoUser;
    }

    public function findAll(array $filters = [])
    {
        $query = $this->service->with(['userBank.bank']);

        $userBankCode = trim((string) ($filters['userBankCode'] ?? ''));
        $startDate = trim((string) ($filters['startDate'] ?? ''));
        $endDate = trim((string) ($filters['endDate'] ?? ''));
        $mutationType = trim((string) ($filters['mutationType'] ?? ''));

        if ($userBankCode !== '') {
            $query->where('userBankCode', $userBankCode);
        }

        $rows = $query->get();

        $hasMutationFilter = $startDate !== '' || $endDate !== '' || $mutationType !== '';
        if (! $hasMutationFilter || $rows->isEmpty()) {
            return $rows;
        }

        $bankCodes = $rows->pluck('userBankCode')->filter()->unique()->values()->all();

        $mutationQuery = Mutation::query()
            ->whereIn('userBankCode', $bankCodes);

        if ($startDate !== '') {
            $mutationQuery->whereDate(DB::raw('COALESCE(date, created_at)'), '>=', $startDate);
        }

        if ($endDate !== '') {
            $mutationQuery->whereDate(DB::raw('COALESCE(date, created_at)'), '<=', $endDate);
        }

        if ($mutationType !== '' && in_array($mutationType, ['In', 'Out'], true)) {
            $mutationQuery->where('type', $mutationType);
        }

        $grouped = $mutationQuery->get()->groupBy('userBankCode');

        return $rows->filter(function ($row) use ($grouped) {
            return $grouped->has($row->userBankCode);
        })->map(function ($row) use ($grouped) {
            $mutations = $grouped->get($row->userBankCode, collect());
            $debit = (float) $mutations->where('type', 'In')->sum('nominal');
            $credit = (float) $mutations->where('type', 'Out')->sum('nominal');

            $clone = clone $row;
            $clone->debit = $debit;
            $clone->credit = $credit;
            $clone->balance = $debit - $credit;

            return $clone;
        })->values();
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
