<?php

namespace App\Models\Purchasing;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseStatus extends Model
{
    use HasFactory, Uuid;

    protected $table = 'purchase_status';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'nama'
    ];
}
