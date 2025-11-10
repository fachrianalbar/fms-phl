<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'position';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'positionCode', 'code');
    }
}
