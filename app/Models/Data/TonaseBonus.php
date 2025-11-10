<?php

namespace App\Models\Data;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TonaseBonus extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'tonase_bonus';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'min',
        'max',
        'value',
    ];
}
