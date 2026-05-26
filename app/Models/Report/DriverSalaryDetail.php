<?php

namespace App\Models\Report;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverSalaryDetail extends Model
{
    use HasFactory, Uuid;

    protected $table = 'driver_salary_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'driverSalaryCode',
        'date',
        'description',
        'type',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'date' => 'date',
    ];

    public function driverSalary()
    {
        return $this->belongsTo(DriverSalary::class, 'driverSalaryCode', 'code');
    }
}
