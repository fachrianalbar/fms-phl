<?php

namespace App\Models\Master;

use App\Models\User;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReceiver extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'bank_receiver';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'bankName',
        'accountNumber',
        'userCode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userCode', 'code');
    }
}
