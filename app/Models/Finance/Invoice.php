<?php

namespace App\Models\Finance;

use App\Models\Master\Customer;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'invoice';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'invoiceNumber',
        'poNumber',
        'receiptNumber',
        'invoiceDate',
        'overdueDate',
        'notes',
        'customerCode',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoiceCode', 'code');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'invoiceCode', 'code');
    }
}
