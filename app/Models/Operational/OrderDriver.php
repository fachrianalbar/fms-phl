<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_driver';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'driverCode',
        'description',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Master\Employee::class, 'driverCode', 'code');
    }
}
