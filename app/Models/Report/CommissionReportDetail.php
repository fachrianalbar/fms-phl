<?php

namespace App\Models\Report;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionReportDetail extends Model
{
    use HasFactory, Uuid;

    protected $table = 'commission_report_detail';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'commissionReportCode',
        'date',
        'description',
        'type',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'date' => 'date',
    ];

    public function commissionReport()
    {
        return $this->belongsTo(CommissionReport::class, 'commissionReportCode', 'code');
    }
}
