<?php

namespace App\Models\Warehouse;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceFifo extends Model
{
    use HasFactory, Uuid;

    protected $table = 'maintenance_fifo';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'maintenanceDetailCode',
        'purchaseDetailCode',
        'qty',
    ];
}
