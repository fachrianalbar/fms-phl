<?php

namespace App\Models\Master;

use App\Models\Bank\BankAccount;
use App\Models\Operational\Order;
use App\Models\Warehouse\Maintenance;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'fleet';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'plateNumber',
        'year',
        'engineNumber',
        'frameNumber',
        'fleetBrandCode',
        'fleetTypeCode',
        'barcode',
        'vehicleRegistrationNumber',
        'insurance',
        'fleetCompanyCode',
        'vehicleRegistrationDueDate',
        'driverCode',
        'barcodeNumber',
        'vehicleTax',
        'vehicleKir',
    ];

    public function brand()
    {
        return $this->belongsTo(FleetBrand::class, 'fleetBrandCode', 'code');
    }

    public function type()
    {
        return $this->belongsTo(FleetType::class, 'fleetTypeCode', 'code');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'fleetCode', 'code');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'fleetCode', 'code');
    }

    public function pictures()
    {
        return $this->hasMany(FleetPicture::class, 'fleetCode', 'code');
    }

    public function company()
    {
        return $this->belongsTo(FleetCompany::class, 'fleetCompanyCode', 'code');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driverCode', 'code');
    }

    public function bank()
    {
        return $this->belongsTo(BankAccount::class, 'bankCode', 'code');
    }
}
