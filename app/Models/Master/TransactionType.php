<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionType extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'transaction_type';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];
}
