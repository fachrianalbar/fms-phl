<?php

namespace App\Models\Warehouse;

use App\Models\Master\Fleet;
use App\Models\Purchasing\Purchase;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'maintenance';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'date',
        'time',
        'fleetCode',
        'warehouseCode',
        'status',
        'grand_total',
    ];

    protected $casts = [
        'grand_total' => 'decimal:2',
    ];

    public function updateGrandTotal()
    {
        $sum = $this->details()->sum('total') ?: 0;
        $this->grand_total = $sum;
        $this->saveQuietly();
    }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleetCode', 'code');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Inventory\Warehouse::class, 'warehouseCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(MaintenanceDetail::class, 'maintenanceCode', 'code');
    }

    /**
     * Purchase Order (PO) yang dipilih untuk maintenance ini.
     */
    public function purchases()
    {
        return $this->belongsToMany(Purchase::class, 'maintenance_purchase', 'maintenance_id', 'purchase_id')
            ->withTimestamps();
    }
}
