<?php

namespace App\Models\Finance;

use App\Models\Bank\UserBank;
use App\Models\Operational\Order;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPaymentHistory extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_payment_history';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'paymentType',
        'total',
        'date',
        'description',
        'userBankCode',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }
}
