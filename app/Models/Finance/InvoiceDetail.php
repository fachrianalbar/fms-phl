<?php

namespace App\Models\Finance;

use App\Models\Operational\Order;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'invoice_detail';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'invoiceCode',
        'orderCode',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoiceCode', 'code');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
