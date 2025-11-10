<?php

namespace App\Models\Data;

use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetType;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetDriver extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'fleet_driver';

    public $incrementing = false;

    protected $fillable = [
        'code',
        // 'driverCode',
        'owner',
        'fleetTypeCode',
        'fleetCode',
        'vehicleRegistrationNumber',
        'vehicleRegistrationNumberExpDate',
        'kir',
        'kirExpDate',
    ];

    // public function employee()
    // {
    //     return $this->belongsTo(Employee::class, 'driverCode', 'code');
    // }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleetCode', 'code');
    }

    public function fleetType()
    {
        return $this->belongsTo(FleetType::class, 'fleetTypeCode', 'code');
    }
}
