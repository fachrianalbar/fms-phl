<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'district';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'city_id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
