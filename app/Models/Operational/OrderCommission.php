<?php

namespace App\Models\Operational;

use App\Models\Master\CostComponent;
use App\Models\Report\CommissionReport;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCommission extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order_commission';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'cost_component_id',
        'order_cost_id',
        'commission_report_id',
        'amount',
        'status',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'payment_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function costComponent()
    {
        return $this->belongsTo(CostComponent::class, 'cost_component_id', 'id');
    }

    public function orderCost()
    {
        return $this->belongsTo(OrderCost::class, 'order_cost_id', 'id');
    }

    public function commissionReport()
    {
        return $this->belongsTo(CommissionReport::class, 'commission_report_id', 'id');
    }
}
