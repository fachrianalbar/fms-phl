<?php

namespace App\Models\Data;

use App\Models\Master\FleetCompany;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutePriceExternal extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'route_price_external';

    public $incrementing = false;

    protected $fillable = [
        'route_id',
        'fleet_company_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', 'id');
    }

    public function fleetCompany()
    {
        return $this->belongsTo(FleetCompany::class, 'fleet_company_id', 'id');
    }
}
