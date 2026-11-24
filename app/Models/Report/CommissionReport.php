<?php

namespace App\Models\Report;

use App\Models\Master\Customer;
use App\Models\Operational\OrderCommission;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionReport extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'commission_report';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'recipientName',
        'customerCode',
        'componentCodes',
        'startDate',
        'endDate',
        'paymentDate',
        'totalCommission',
        'totalAdjustment',
        'grandTotal',
        'notes',
    ];

    protected $casts = [
        'componentCodes' => 'array',
        'totalCommission' => 'decimal:2',
        'totalAdjustment' => 'decimal:2',
        'grandTotal' => 'decimal:2',
        'startDate' => 'date',
        'endDate' => 'date',
        'paymentDate' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(CommissionReportDetail::class, 'commissionReportCode', 'code');
    }

    public function orderCommissions()
    {
        return $this->hasMany(OrderCommission::class, 'commission_report_id', 'id');
    }
}
