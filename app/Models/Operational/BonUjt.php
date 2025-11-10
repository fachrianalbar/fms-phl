<?php

namespace App\Models\Operational;

use App\Models\Master\FleetType;
use App\Models\User;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonUjt extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'bon_ujt';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'bon',
        'date',
        'time',
        'handover',
        'note',
        'createdBy',
        'submitDate',
        'fleetTypeCode',
    ];

    public function user()
    {
        return $this->BelongsTo(User::class, 'createdBy', 'id');
    }

    public function fleetType()
    {
        return $this->belongsTo(FleetType::class, 'fleetTypeCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(BonUjtDetail::class, 'bonUjtCode', 'code');
    }
}
