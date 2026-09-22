<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'employee';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'positionCode',
        'phone',
        'email',
        'birthDate',
        'joinDate',
        'ktp',
        'npwp',
        'gender',
        'address',
        'photo',
        'employeeStatus',
        'status',
        'nik',
        'provinceId',
        'cityId',
        'districtId',
        'birthPlace',
        'citizenship',
        'bankCode',
        'accountNumber',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function isDriver(): bool
    {
        return in_array($this->positionCode, ['KP_240823034043', 'FPS250612034049']);
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'positionCode', 'code');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'provinceId', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'cityId', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'districtId', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(\App\Models\Bank\BankAccount::class, 'bankCode', 'code');
    }

    public function orderDriverSalaries()
    {
        return $this->hasMany(\App\Models\Operational\OrderDriverSalary::class, 'driver_id', 'id');
    }
}
