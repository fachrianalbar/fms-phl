<?php

namespace App\Models\Finance;

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
        'nominal',
        'description',
        'amount',
        'paymentReceipt',
        'userBankCode',
    ];
}
