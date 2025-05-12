<?php

namespace App\Models\Master;

use App\Models\Operational\DownPayment;
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
        'nik',
        'provinceId',
        'cityId',
        'districtId',
        'address',
        'birthPlace',
        'gender',
        'citizenship'
    ];

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

    public function downPayment()
    {
        return $this->hasMany(DownPayment::class, 'driverCode', 'code');
    }
}
