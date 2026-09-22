<?php

namespace App\Models\Warehouse;

use App\Models\Purchasing\Purchase;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class MaintenancePurchase extends Model
{
    use Uuid;

    protected $table = 'maintenance_purchase';

    public $incrementing = false;

    protected $fillable = [
        'maintenance_id',
        'purchase_id',
    ];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }
}
