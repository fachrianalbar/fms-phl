<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostComponentPriceLog extends Model
{
    use HasFactory, Uuid;

    protected $table = 'cost_component_price_logs';

    public $incrementing = false;

    protected $fillable = [
        'costComponentCode',
        'costComponentName',
        'oldPrice',
        'newPrice',
        'changedBy',
        'notes',
    ];

    protected $casts = [
        'oldPrice' => 'decimal:2',
        'newPrice' => 'decimal:2',
    ];

    public function costComponent()
    {
        return $this->belongsTo(CostComponent::class, 'costComponentCode', 'code');
    }
}
