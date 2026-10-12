<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetPlateHistory extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'fleet_plate_histories';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fleet_id',
        'fleet_code',
        'old_plate_number',
        'new_plate_number',
        'change_date',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'change_date' => 'date',
    ];

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class, 'fleet_id', 'id');
    }
}
