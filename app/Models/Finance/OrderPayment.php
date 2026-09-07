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
        'additional_cost',
        'claim',
        'claim_description',
        'ppn',
        'ppn_percent',
        'pph',
        'pph_percent',
        'total',
        'status',
    ];

    protected $casts = [
        'cost' => 'float',
        'additional_cost' => 'float',
        'claim' => 'float',
        'ppn' => 'float',
        'ppn_percent' => 'float',
        'pph' => 'float',
        'pph_percent' => 'float',
        'total' => 'float',
        'status' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
