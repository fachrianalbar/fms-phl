<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetCompany extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'fleet_company';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
    ];
}
