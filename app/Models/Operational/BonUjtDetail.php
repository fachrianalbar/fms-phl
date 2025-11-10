<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonUjtDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'bon_ujt_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'bonUjtCode',
        'orderCode',
    ];
}
