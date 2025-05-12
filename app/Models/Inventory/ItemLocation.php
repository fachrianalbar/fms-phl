<?php

namespace App\Models\Inventory;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemLocation extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'item_location';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'warehouseCode'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouseCode', 'code');
    }
}
