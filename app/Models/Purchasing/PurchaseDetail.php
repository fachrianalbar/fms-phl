<?php

namespace App\Models\Purchasing;

use App\Models\Inventory\Item;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'purchase_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'itemCode',
        'purchaseCode',
        'qty',
        'status',
        'receivedQty',
        'qtyUsed',
        'price',
        'description',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchaseCode', 'code');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'itemCode', 'code');
    }
}
