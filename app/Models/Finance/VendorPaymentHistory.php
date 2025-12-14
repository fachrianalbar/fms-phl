<?php

namespace App\Models\Finance;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPaymentHistory extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'vendor_payment_history';

    public $incrementing = false;

    protected $fillable = [
        'vendor_payment_id',
        'amount',
        'payment_date',
        'user_bank_code',
        'description',
    ];

    public function vendorPayment()
    {
        return $this->belongsTo(VendorPayment::class, 'vendor_payment_id', 'id');
    }
}
