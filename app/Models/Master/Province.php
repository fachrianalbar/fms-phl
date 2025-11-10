<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'province';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function cities()
    {
        return $this->hasMany(City::class, 'province_id', 'id');
    }
}
