<?php

namespace App\Models\Operational;

use App\Models\Master\CostComponent;
use App\Models\Master\Employee;
use App\Models\Report\DriverSalary;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDriverSalary extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order_driver_salary';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'driver_id',
        'driver_salary_id',
        'cost_component_id',
        'order_cost_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id', 'id');
    }

    public function driverSalary()
    {
        return $this->belongsTo(DriverSalary::class, 'driver_salary_id', 'id');
    }

    public function costComponent()
    {
        return $this->belongsTo(CostComponent::class, 'cost_component_id', 'id');
    }

    public function orderCost()
    {
        return $this->belongsTo(OrderCost::class, 'order_cost_id', 'id');
    }
}
