<?php

namespace App\Models\Master;

use App\Models\Operational\CustomerDetailOrder;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDetail extends Model
{
    use HasFactory, Uuid;

    protected $table = 'customer_detail';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'customerCode',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function customerDetailOrders()
    {
        return $this->hasMany(CustomerDetailOrder::class, 'customerDetailCode', 'code');
    }
}
