<?php

namespace App\Models\Operational;

use App\Models\Master\CustomerDetail;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDetailOrder extends Model
{
    use HasFactory, Uuid;

    protected $table = 'customer_detail_order';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'value',
        'orderCode',
        'customerDetailCode',
    ];

    public function customerDetail()
    {
        return $this->belongsTo(CustomerDetail::class, 'customerDetailCode', 'code');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
