<?php

namespace App\Models\Bank;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferFund extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'transfer_fund';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'mutationCode',
        'type',
        'receiver',
        'sender',
    ];

    public function receiverUserBank()
    {
        return $this->belongsTo(UserBank::class, 'receiver', 'code');
    }

    public function senderUserBank()
    {
        return $this->belongsTo(UserBank::class, 'sender', 'code');
    }
}
