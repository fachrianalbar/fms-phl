<?php

namespace App\Models\Finance;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePaymentClaim extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'invoice_payment_claim';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'transactionCode',
        'invoiceCode',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(InvoicePaymentTransaction::class, 'transactionCode', 'code');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceCode', 'code');
    }
}
