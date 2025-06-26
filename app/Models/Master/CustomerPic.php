<?php

namespace App\Models\Master;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPic extends Model
{
    use HasFactory, Uuid;

    protected $table = 'customer_pic';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'picName',
        'phone',
        'customerCode'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }
}
