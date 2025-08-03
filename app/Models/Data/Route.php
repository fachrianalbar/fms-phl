<?php

namespace App\Models\Data;

use App\Models\Master\Customer;
use App\Models\Master\FleetType;
use App\Models\Master\Location;
use App\Models\Master\RouteType;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'route';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'customerCode',
        'originLocationCode',
        'destinationLocationCode',
        'fleetTypeCode',
        'price',
        'vendorPrice',
        'routeTypeCode',
        'personalVendorPrice',
        'description'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function originLocation()
    {
        return $this->belongsTo(Location::class, 'originLocationCode', 'code');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destinationLocationCode', 'code');
    }

    public function fleetType()
    {
        return $this->belongsTo(FleetType::class, 'fleetTypeCode', 'code');
    }

    public function routeDetail()
    {
        return $this->hasMany(RouteDetail::class, 'routeCode', 'code');
    }

    public function routeType()
    {
        return $this->belongsTo(RouteType::class, 'routeTypeCode', 'code');
    }
}
