<?php

namespace App\Services\Bank;

use App\Helpers\GenerateCode;
use App\Helpers\LiveMutationHelper;
use App\Models\Bank\ConfigBank;
use App\Models\Bank\TransferFund;
use App\Models\Bank\UserBank;
use App\Models\Mutation;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Auth;

class TransferFundService
{
    use LogActivity;

    protected $service;
    protected $mutation;
    protected $userBank;
    protected $configBank;

    public function __construct(TransferFund $transferFund, Mutation $mutation, UserBank $userBank, ConfigBank $configBank)
    {
        $this->service = $transferFund;
        $this->mutation = $mutation;
        $this->userBank = $userBank;
        $this->configBank = $configBank;
    }

    public function findAll()
    {


        $configBank = $this->configBank->where('userCode', Auth::user()->code)->get();

        return $this->mutation
            ->where('transactionTypeCode', 'FTT250405160111')
            ->whereHas('cash', function ($q) use ($configBank) {
                $q->where(function ($query) use ($configBank) {
                    $query->whereIn('sender', $configBank->pluck('userBankCode'))
                        ->orWhereIn('receiver', $configBank->pluck('userBankCode'));
                })
                    ->where('type', 'In');
            })
            ->with(['cash', 'userBank'])
            ->latest()
            ->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {

        $sender = $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->sender,
            'date' => $request->date . ' ' . $request->time,
            'description' => $request->description,
            'nominal' => (int)$request->nominal,
            'type' => "Out",
            'transactionTypeCode' => "FTT250405160111",
        ]);

        LiveMutationHelper::updateLiveMutation($request->sender, (int)$request->nominal, 'credit');

        $this->service->create([
            'code' => $request->code,
            'mutationCode' => $sender->code,
            'type' => "Out",
            'sender' => $request->sender,
            'receiver' => $request->receiver,

        ]);

        $userBankSender = $this->userBank->where('code', $request->sender)->with(['bank'])->first();


        $receiver = $this->mutation->create([
            'code' => GenerateCode::generateCode('FMT'),
            'userBankCode' => $request->receiver,
            'date' => $request->date . ' ' . $request->time,
            'description' => 'Cash transfer with amount ' . number_format((int)$request->nominal, 0, ',', '.') . " from " . $userBankSender->bank->name . ' - ' . $userBankSender->accountNumber . ' - ' . $userBankSender->accountName . " (" . $request->description . ")",
            'nominal' => (int)$request->nominal,
            'type' => "In",
            'transactionTypeCode' => "FTT250405160111",
        ]);

        LiveMutationHelper::updateLiveMutation($request->receiver, (int)$request->nominal, 'debit');

        $this->service->create([
            'code' => $request->code,
            'mutationCode' => $receiver->code,
            'type' => "In",
            'sender' => $request->sender,
            'receiver' => $request->receiver,

        ]);

        $this->logActivity($title, $sender, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'date' => $request->date,
            'time' => $request->time,
            'description' => $request->description,
            'nominal' => (int)$request->nominal,
            'type' => 'Cash',
            'transferType' => $request->transferType,
            'receiver' => $request->receiver,
            'bankSender' => $request->bankSender,
            'bankReceiver' => $request->bankReceiver,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
