<?php

namespace App\Models\Inventory;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'stock';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'itemCode',
        'stockIn',
        'stockOut',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'itemCode', 'code');
    }
}
