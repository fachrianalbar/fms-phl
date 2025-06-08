<?php

namespace App\Models\Operational;

use App\Models\Master\Customer;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDetailOrder extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'customer_detail';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'value',
        'orderCode',
        'customerCode',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
