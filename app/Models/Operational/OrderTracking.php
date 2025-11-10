<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTracking extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order_tracking';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'latitude',
        'longitude',
        'orderCode',
    ];
}
