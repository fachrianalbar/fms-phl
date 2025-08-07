<?php

namespace App\Models\Operational;

use App\Models\Master\Material;
use App\Models\Master\Unit;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMaterial extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_material';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'materialCode',
        'unitCode',
        'materialQty'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'materialCode', 'code');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitCode', 'code');
    }
}
