<?php

namespace App\Models\Finance;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPaymentBatch extends Model
{
    use HasFactory, Uuid;

    protected $table = 'vendor_payment_batch';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'request_key',
        'payload_hash',
        'status',
        'payment_date',
        'user_bank_code',
        'amount',
        'nota_count',
        'order_count',
        'fully_paid_count',
        'partial_count',
        'description',
        'cancelled_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cancelled_at' => 'datetime',
        'amount' => 'integer',
        'nota_count' => 'integer',
        'order_count' => 'integer',
        'fully_paid_count' => 'integer',
        'partial_count' => 'integer',
    ];
}
