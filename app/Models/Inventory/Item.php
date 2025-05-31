<?php

namespace App\Models\Inventory;

use App\Models\Master\Unit;
use App\Models\Purchasing\PurchaseDetail;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'item';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'brandName',
        'categoryCode',
        'itemLocationCode',
        'warehouseCode',
        'unitCode',
        'supplierCode',
        'price'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitCode', 'code');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'categoryCode', 'code');
    }

    public function location()
    {
        return $this->belongsTo(ItemLocation::class, 'itemLocationCode', 'code');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseCode', 'code');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierCode', 'code');
    }

    public function latestPurchase()
    {
        return $this->hasOne(PurchaseDetail::class, 'itemCode', 'code')->latest();
    }
}
