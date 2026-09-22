<?php

namespace App\Models\Purchasing;

use App\Models\Bank\UserBank;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentBatch extends Model
{
    use HasFactory, Uuid;

    protected $table = 'supplier_payment_batch';

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
        'purchase_count',
        'fully_paid_count',
        'partial_count',
        'description',
        'cancelled_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cancelled_at' => 'datetime',
        'amount' => 'float',
        'purchase_count' => 'integer',
        'fully_paid_count' => 'integer',
        'partial_count' => 'integer',
    ];

    public function histories()
    {
        return $this->hasMany(PurchasePaymentHistory::class, 'batch_code', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'user_bank_code', 'code');
    }
}
