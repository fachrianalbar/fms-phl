<?php

namespace App\Models\Master;

use App\Models\Data\RoutePriceExternal;
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
        'accountNumber',
        'bankName',
        'pph',
    ];

    public function routePriceExternal()
    {
        return $this->hasMany(RoutePriceExternal::class, 'fleet_company_id', 'id');
    }
}
