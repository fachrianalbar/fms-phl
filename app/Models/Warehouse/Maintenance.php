<?php

namespace App\Models\Warehouse;

use App\Models\Master\Fleet;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'maintenance';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'date',
        'time',
        'fleetCode',
        'status',
    ];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleetCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(MaintenanceDetail::class, 'maintenanceCode', 'code');
    }
}
