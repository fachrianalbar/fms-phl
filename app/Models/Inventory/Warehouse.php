<?php

namespace App\Models\Inventory;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'warehouse';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'address',
    ];
}
