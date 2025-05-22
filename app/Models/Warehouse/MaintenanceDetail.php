<?php

namespace App\Models\Warehouse;

use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'maintenance_detail';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'qty',
        'itemCode',
        'maintenanceCode',
        'purchaseDetailCode',
        'status'
    ];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenanceCode', 'code');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'itemCode', 'code');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'itemCode', 'itemCode');
    }
}
