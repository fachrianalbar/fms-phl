<?php

namespace App\Models\Finance;

use App\Models\Operational\Order;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPayment extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'vendor_payment';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'description',
        'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function mutation()
    {
        return $this->hasOne(\App\Models\Mutation::class, 'transactionTypeCode', 'code');
    }

    public function paymentHistory()
    {
        return $this->hasMany(\App\Models\Finance\VendorPaymentHistory::class, 'vendor_payment_id', 'id');
    }
}
