<?php

namespace App\Models\Master;

use App\Models\Data\Route;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'customer';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'address1',
        'address2',
        'phone',
        'accountNumber',
        'ppn',
        'pph',
        'invoiceFormat',
        'nickname',
        'picName',
        'email',
        'npwp',
        'telegramUsername'
    ];

    public function routes()
    {
        return $this->hasMany(Route::class, 'customerCode');
    }
}
