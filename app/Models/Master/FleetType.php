<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetType extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'fleet_type';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];
}
