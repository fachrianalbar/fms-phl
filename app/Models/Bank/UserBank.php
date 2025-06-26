<?php

namespace App\Models\Bank;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBank extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'user_bank';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'accountNumber',
        'accountName',
        'type',
        'bankCode',
        'accountNUmber',
        'balance'
    ];

    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'bankCode', 'code');
    }
}
