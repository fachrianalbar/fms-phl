<?php

namespace App\Models\Data;

use App\Models\Master\Location;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupLocation extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'pickup_location';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'longitude',
        'latitude',
        'address',
        'description',
        'locationCode',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'locationCode', 'code');
    }
}
