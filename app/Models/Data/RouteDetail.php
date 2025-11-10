<?php

namespace App\Models\Data;

use App\Models\Master\CostComponent;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'route_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'type',
        'amount',
        'percentage',
        'componentCode',
        'routeCode',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class, 'routeCode', 'code');
    }

    public function costComponent()
    {
        return $this->belongsTo(CostComponent::class, 'componentCode', 'code');
    }
}
