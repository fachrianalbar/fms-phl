<?php

namespace App\Models\Warehouse;

use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'maintenance_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'qty',
        'itemCode',
        'maintenanceCode',
        'status',
        'price',
        'total',
    ];

    protected $casts = [
        'qty' => 'decimal:1',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->price)) {
                $price = Item::where('code', $model->itemCode)->value('price');
                $model->price = $price ?? 0;
            }
            $model->total = bcmul((string)$model->qty, (string)$model->price, 2);
        });

        static::saved(function ($model) {
            if ($model->maintenance) {
                $model->maintenance->updateGrandTotal();
            }
        });

        static::deleted(function ($model) {
            if ($model->maintenance) {
                $model->maintenance->updateGrandTotal();
            }
        });
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenanceCode', 'code');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'itemCode', 'code');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'itemCode', 'itemCode');
    }
}
