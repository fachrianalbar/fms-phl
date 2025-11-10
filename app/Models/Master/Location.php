<?php

namespace App\Models\Master;

use App\Models\Data\DropLocation;
use App\Models\Data\PickupLocation;
use App\Models\Data\Route;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'location';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'provinceId',
        'cityId',
        'districtId',
        'address',
        'latitude',
        'longitude',
    ];

    public function pickupLocations()
    {
        return $this->hasMany(PickupLocation::class, 'locationCode', 'code');
    }

    public function dropLocations()
    {
        return $this->hasMany(DropLocation::class, 'locationCode', 'code');
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

    public function originLocation()
    {
        return $this->hasMany(Route::class, 'originLocationCode', 'code');
    }

    public function destinationLocation()
    {
        return $this->hasMany(Route::class, 'destinationLocationCode', 'code');
    }
}
