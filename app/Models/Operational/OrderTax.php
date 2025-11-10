<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTax extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'order_tax';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'description',
        'orderCode',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderCode', 'code');
    }
}
