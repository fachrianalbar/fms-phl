<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetBrand extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'fleet_brand';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];
}
