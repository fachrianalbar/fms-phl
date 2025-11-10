<?php

namespace App\Models;

use App\Models\Bank\Expense;
use App\Models\Bank\TransferFund;
use App\Models\Bank\UserBank;
use App\Models\Master\TransactionType;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mutation extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'mutation';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'userBankCode',
        'date',
        'description',
        'nominal',
        'type',
        'transactionTypeCode',
        'transactionCode',
    ];

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class, 'transactionTypeCode', 'code');
    }

    public function expense()
    {
        return $this->hasOne(Expense::class, 'mutationCode', 'code');
    }

    public function cash()
    {
        return $this->hasOne(TransferFund::class, 'mutationCode', 'code');
    }
}
