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
        'claim',
        'claim_description',
        'ppn',
        'ppn_percent',
        'pph',
        'pph_percent',
        'total',
        'date',
        'description',
        'userBankCode',
        'batch_code',
    ];

    protected $casts = [
        'claim' => 'float',
        'ppn' => 'float',
        'ppn_percent' => 'float',
        'pph' => 'float',
        'pph_percent' => 'float',
        'total' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }

    public function orderPayment()
    {
        return $this->belongsTo(OrderPayment::class, 'orderCode', 'orderCode');
    }
}
