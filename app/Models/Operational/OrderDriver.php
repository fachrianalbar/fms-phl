<?php

namespace App\Models\Operational;

use App\Models\Master\Employee;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_driver';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'orderCode',
        'driverCode',
        'driverName',
        'description',
    ];

    protected static function booted()
    {
        static::saving(function (OrderDriver $orderDriver) {
            if ($orderDriver->isDirty('driverCode') || empty($orderDriver->getRawOriginal('driverName'))) {
                $orderDriver->driverName = empty($orderDriver->driverCode)
                    ? null
                    : Employee::where('code', $orderDriver->driverCode)->value('name');
            }
        });
    }

    /**
     * Nama supir snapshot pada saat perubahan supir dicatat.
     */
    public function getDriverNameAttribute($value)
    {
        return $value ?: ($this->driver?->name);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Master\Employee::class, 'driverCode', 'code');
    }
}
