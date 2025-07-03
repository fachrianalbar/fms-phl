<?php

namespace App\Models\Finance;

use App\Models\Operational\Order;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_payment';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'cost',
        'pph',
        'total',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
