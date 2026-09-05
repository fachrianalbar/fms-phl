<?php

namespace App\Models\Finance;

use App\Models\Bank\UserBank;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePayment extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'invoice_payment';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'paymentDate',
        'invoiceCode',
        'transactionCode',
        'nominal',
        'description',
        'amount',
        'ppnAmount',
        'pphAmount',
        'paymentReceipt',
        'userBankCode',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'amount' => 'integer',
        'ppnAmount' => 'integer',
        'pphAmount' => 'integer',
    ];

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceCode', 'code');
    }

    public function transaction()
    {
        return $this->belongsTo(InvoicePaymentTransaction::class, 'transactionCode', 'code');
    }
}
