<?php

namespace App\Models\Purchasing;

use App\Models\Bank\UserBank;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePaymentHistory extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'purchase_payment_histories';

    public $incrementing = false;

    protected $fillable = [
        'purchaseCode',
        'amount',
        'paymentDate',
        'userBankCode',
        'description',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchaseCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }
}
