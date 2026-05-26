<?php

namespace App\Models\Report;

use App\Models\Master\Employee;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverSalary extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'driver_salary';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'driverCode',
        'startDate',
        'endDate',
        'totalSalary',
        'totalAdjustment',
        'grandTotal',
        'notes',
    ];

    protected $casts = [
        'totalSalary' => 'decimal:2',
        'totalAdjustment' => 'decimal:2',
        'grandTotal' => 'decimal:2',
        'startDate' => 'date',
        'endDate' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driverCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(DriverSalaryDetail::class, 'driverSalaryCode', 'code');
    }
}
