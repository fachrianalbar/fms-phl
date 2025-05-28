<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;


class FleetCompany extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'fleet_company';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'type'
    ];
}
