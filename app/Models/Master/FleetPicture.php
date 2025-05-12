<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetPicture extends Model
{
    use HasFactory, Uuid;

    protected $table = 'fleet_picture';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'fleetCode',
        'fleetPicture',
    ];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleetCode', 'code');
    }
}
