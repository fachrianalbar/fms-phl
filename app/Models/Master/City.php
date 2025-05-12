<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'city';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'province_id'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
