<?php

namespace App\Models;

use App\Models\Bank\UserBank;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveMutation extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'live_mutation';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'userBankCode',
        'debit',
        'credit',
        'balance',
    ];

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }
}
