<?php

namespace App\Models\Bank;

use App\Models\User;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigBank extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'config_bank';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'userCode',
        'userBankCode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }
}
