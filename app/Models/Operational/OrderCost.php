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
        'driverCode',
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

    public function driver()
    {
        return $this->belongsTo(\App\Models\Master\Employee::class, 'driverCode', 'code');
    }

    public function orderDriverSalary()
    {
        return $this->hasOne(OrderDriverSalary::class, 'order_cost_id', 'id');
    }
}
