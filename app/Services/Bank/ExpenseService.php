<?php

namespace App\Services\Bank;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\Expense;
use App\Models\LiveMutation;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    use LogActivity;

    protected $service;

    protected $mutation;

    protected $liveMutation;

    public function __construct(Expense $expense, Mutation $mutation, LiveMutation $liveMutation)
    {
        $this->service = $expense;
        $this->mutation = $mutation;
        $this->liveMutation = $liveMutation;
    }

    public function findAll()
    {
        // return $this->service->with(['cashSender'])->where('createdBy', Auth::user()->code)->whereIn('type', ['Expense', 'Expense Office'])->orderBy('created_at', 'desc')->get();
        return $this->mutation->whereIn('transactionTypeCode', ['FTT250403152955', 'FTT250403153003'])->with(['expense', 'userBank', 'userBank.bank'])->latest()->get();
    }

    public function getById($id)
    {
        return $this->mutation->where('id', $id)->with(['expense'])->first();
    }

    public function store($request, $title)
    {

        $data = $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->userBankCode,
            'date' => $request->date.' '.$request->time,
            'description' => $request->transactionTypeName.' with amount '.number_format((int) $request->nominal, 0, ',', '.').' to driver '.$request->driverName.' ('.$request->description.')',
            'nominal' => (int) $request->nominal,
            'type' => 'Out',
            'transactionTypeCode' => $request->transactionTypeCode,
        ]);

        LiveMutationHelper::updateLiveMutation($request->userBankCode, (int) $request->nominal, 'credit');

        $this->service->create([
            'code' => $request->code,
            'mutationCode' => $data->code,
            'createdBy' => $request->createdBy,
            'driverCode' => $request->driverCode,
            'createdBy' => Auth::user()->code,

        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $mutation = $this->mutation->where('id', $id)->firstOrFail();
        $expense = $this->service->where('mutationCode', $mutation->code)->firstOrFail();

        $oldBank = $mutation->userBankCode;
        $newBank = $request->userBankCode;
        $oldNominal = (int) $mutation->nominal;
        $newNominal = (int) $request->nominal;

        $liveMutation = LiveMutation::where('userBankCode', $newBank)->first();

        if ($oldBank === $newBank) {
            $difference = $newNominal - $oldNominal;
            if ($difference > 0 && $difference > $liveMutation->balance) {
                throw new \Exception('Insufficient balance to increase the expense amount.');
            }
        } else {
            if ($newNominal > $liveMutation->balance) {
                throw new \Exception('Insufficient balance in the selected bank account.');
            }
        }

        if ($oldBank !== $newBank || $oldNominal !== $newNominal) {
            LiveMutationHelper::updateLiveMutation($oldBank, $oldNominal, 'debit');
            LiveMutationHelper::updateLiveMutation($newBank, $newNominal, 'credit');
        }

        $mutation->update([
            'userBankCode' => $newBank,
            'date' => $request->date.' '.$request->time,
            'description' => $request->description,
            'nominal' => $newNominal,
            'transactionTypeCode' => $request->transactionTypeCode,
        ]);

        $expense->update([
            'driverCode' => $request->driverCode,
            'updatedBy' => Auth::user()->code,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $data = $this->getById($id);

        $this->service->where('mutationCode', $data->code)->delete();

        $data->delete();
    }
}
