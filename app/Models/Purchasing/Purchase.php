<?php

namespace App\Models\Purchasing;

use App\Models\Bank\UserBank;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'purchase';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'date',
        'time',
        'supplierCode',
        'warehouseCode',
        'status',
        'receivedDate',
        'paymentDate',
        'nominal',
        'paymentCode',
        'userBankCode',
        'dueDate',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchaseCode', 'code');
    }

    public function purchaseStatus()
    {
        return $this->belongsTo(PurchaseStatus::class, 'status', 'code');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PurchasePaymentHistory::class, 'purchaseCode', 'code');
    }
}
