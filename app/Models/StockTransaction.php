<?php

namespace App\Models;

use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\Warehouse\MaintenanceDetail;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransaction extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'stock_transaction';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'itemCode',
        'warehouseCode',
        'transactionCode',
        'transactionDetailCode',
        'qtyIn',
        'qtyOut',
        'date',
        'transactionType',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'itemCode', 'code');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseCode', 'code');
    }

    public function purchase()
    {
        return $this->belongsTo(PurchaseDetail::class, 'transactionCode', 'code');
    }

    public function maintenance()
    {
        return $this->belongsTo(MaintenanceDetail::class, 'transactionCode', 'code');
    }
}
