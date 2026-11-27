<?php

namespace App\Models\Finance;

use App\Models\Mutation;
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

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'orderCode',
        'amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'nota_number',
        'user_bank_code',
        'ppn_amount',
        'ppn_rate',
        'pph_amount',
        'pph_rate',
        'claim_amount',
        'description',
        'date',
        'tax_invoice_number',
        'withholding_tax_number',
        'withholding_tax_amount',
        'withholding_tax_date',
    ];

    protected $casts = [
        'withholding_tax_amount' => 'decimal:2',
        'withholding_tax_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function mutation()
    {
        return $this->hasOne(Mutation::class, 'transactionTypeCode', 'code');
    }

    public function paymentHistory()
    {
        return $this->hasMany(VendorPaymentHistory::class, 'vendor_payment_id', 'id');
    }
}
