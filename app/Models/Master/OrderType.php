<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderType extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order_type';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];
}
