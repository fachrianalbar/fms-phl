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

    protected $casts = [
        'qty' => 'decimal:1',
        'receivedQty' => 'decimal:1',
        'qtyUsed' => 'decimal:1',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchaseCode', 'code');
    }

    public function item()
    {
        // withTrashed: riwayat pembelian tetap menampilkan nama item
        // meskipun item sudah di-soft-delete.
        return $this->belongsTo(Item::class, 'itemCode', 'code')->withTrashed();
    }
}
