<?php

namespace App\Models\Operational;

use App\Models\Master\CostComponent;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_cost';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'componentType',
        'orderCode',
        'nominal',
        'type',
        'description',
        'is_route',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function costComponent()
    {
        return $this->belongsTo(CostComponent::class, 'componentType', 'code');
    }
}
