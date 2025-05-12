<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory, Uuid;

    protected $table = 'order_status';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];
}
