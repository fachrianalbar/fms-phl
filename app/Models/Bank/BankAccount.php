<?php

namespace App\Models\Bank;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'bank_account';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'bankCode',
        'name',
    ];
}
