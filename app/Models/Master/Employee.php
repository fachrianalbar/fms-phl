<?php

namespace App\Models\Master;

use App\Models\Bank\BankAccount;
use App\Models\Operational\OrderDriverSalary;
use App\Traits\Uuid;
use Carbon\Carbon;
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
        'sim',
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

    /**
     * Masa kerja dihitung dari joinDate sampai hari ini, mis. "2 Tahun 3 Bulan".
     */
    public function getWorkDurationAttribute(): string
    {
        if (empty($this->joinDate)) {
            return '-';
        }

        try {
            $joinDate = Carbon::parse($this->joinDate)->startOfDay();
        } catch (\Throwable) {
            return '-';
        }

        $today = Carbon::today();
        if ($joinDate->greaterThan($today)) {
            return '-';
        }

        $diff = $joinDate->diff($today);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' Tahun';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' Bulan';
        }

        return $parts === [] ? '< 1 Bulan' : implode(' ', $parts);
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
        return $this->belongsTo(BankAccount::class, 'bankCode', 'code');
    }

    public function orderDriverSalaries()
    {
        return $this->hasMany(OrderDriverSalary::class, 'driver_id', 'id');
    }
}
