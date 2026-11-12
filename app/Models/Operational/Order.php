<?php

namespace App\Models\Operational;

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
    protected $keyType = 'string';

    protected $casts = [
        'routeAmount' => 'decimal:2',
        'vendorPrice' => 'decimal:2',
        'vendorPriceSingle' => 'decimal:2',
        'personalVendorPrice' => 'decimal:2',
        'price' => 'decimal:2',
        'personalVendorPriceSingle' => 'decimal:2',
        'qty' => 'float',
    ];

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
        'routeCode',
        'qty',
        'orderTypeCode',
        'fleetTypeCode',
        'bonUjt',
        'driverCode',
        'driverName',
        'fleetCode',
        'plateNumber',
        'status',
        'companyCode',
        'estimatedTime',
        'distance',
        'returnDate',
        'returnDescription',
        'routeAmount',
        'price',
        'vendorPrice',
        'vendorPriceSingle',
        'personalVendorPrice',
        'personalVendorPriceSingle',
    ];

    protected static function booted()
    {
        static::saving(function (Order $order) {
            if ($order->isDirty('fleetCode') || empty($order->plateNumber)) {
                if (! empty($order->fleetCode)) {
                    $fleet = Fleet::where('code', $order->fleetCode)->first();
                    if ($fleet && ! empty($fleet->plateNumber)) {
                        $order->plateNumber = $fleet->plateNumber;
                    }
                }
            }

            // Snapshot nama supir hanya diambil ulang saat supir berubah atau snapshot belum ada.
            if ($order->isDirty('driverCode') || empty($order->getRawOriginal('driverName'))) {
                $order->driverName = empty($order->driverCode)
                    ? null
                    : Employee::where('code', $order->driverCode)->value('name');
            }
        });
    }

    public function getPlateNumberAttribute($value)
    {
        return $value ?: ($this->fleet?->plateNumber);
    }

    /**
     * Nama supir snapshot saat transaksi dibuat/diubah.
     * Fallback ke master employee hanya untuk data lama yang belum punya snapshot.
     */
    public function getDriverNameAttribute($value)
    {
        return $value ?: ($this->driver?->name);
    }

    public function scopeWherePlateNumberLike($query, string $plate)
    {
        return $query->where(function ($q) use ($plate) {
            $q->where('order.plateNumber', 'like', "%{$plate}%")
                ->orWhereHas('fleet', function ($fleet) use ($plate) {
                    $fleet->where('plateNumber', 'like', "%{$plate}%");
                });
        });
    }

    public function scopeWhereDriverNameLike($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->where('order.driverName', 'like', "%{$name}%")
                ->orWhereHas('driver', function ($driver) use ($name) {
                    $driver->where('name', 'like', "%{$name}%");
                });
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'materialCode', 'code');
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
        return $this->hasMany(OrderMaterial::class, 'orderCode', 'code')->orderBy('code', 'asc');
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

    public function orderDriverSalaries()
    {
        return $this->hasMany(OrderDriverSalary::class, 'order_id', 'id');
    }

    public function invoiceDetail()
    {
        return $this->hasOne(\App\Models\Finance\InvoiceDetail::class, 'orderCode', 'code');
    }
}
