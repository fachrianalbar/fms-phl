<?php

namespace App\Models\Operational;

use App\Models\Data\FleetDriver;
use App\Models\Data\Route;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\Finance\VendorPayment;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\Material;
use App\Models\Master\OrderType;
use App\Models\Master\Unit;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'shipmentNumber',
        'orderDate',
        'shipmentDate',
        'customerCode',
        'materialCode',
        'unitCode',
        'materialQty',
        'notes',
        'salesOrder',
        'sto',
        'fleetDriverCode',
        'routeCode',
        'qty',
        'routeCode',
        'orderTypeCode',
        'fleetTypeCode',
        'bonUjt',
        'driverCode',
        'fleetCode',
        'status',
        'companyCode',
        'estimatedTime',
        'distance',
        'returnDate',
        'is_order_tax',
        'returnDescription',
        'routeAmount',
        'vendorPrice',
        'personalVendorPrice',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'materialCode', 'code');
    }

    public function fleetDriver()
    {
        return $this->belongsTo(FleetDriver::class, 'fleetDriverCode', 'code');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driverCode', 'code');
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'routeCode', 'code');
    }

    public function cost()
    {
        return $this->hasMany(OrderCost::class, 'orderCode', 'code');
    }

    public function onChargeCost()
    {
        return $this->hasMany(OrderCost::class, 'orderCode', 'code')->where('type', 'On Charge');
    }

    public function orderType()
    {
        return $this->belongsTo(OrderType::class, 'orderTypeCode', 'code');
    }

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleetCode', 'code');
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status', 'code');
    }

    public function orderPayment()
    {
        return $this->hasOne(OrderPayment::class, 'orderCode', 'code');
    }

    public function orderPaymentHistory()
    {
        return $this->hasMany(OrderPaymentHistory::class, 'orderCode', 'code');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitCode', 'code');
    }

    public function orderMaterial()
    {
        return $this->hasMany(OrderMaterial::class, 'orderCode', 'code');
    }

    public function orderDrivers()
    {
        return $this->hasMany(OrderDriver::class, 'orderCode', 'code');
    }

    public function customerDetailOrders()
    {
        return $this->hasMany(CustomerDetailOrder::class, 'orderCode', 'code');
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class, 'orderCode', 'code');
    }
}
