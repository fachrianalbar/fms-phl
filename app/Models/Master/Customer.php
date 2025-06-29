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
        'officeAddress',
        'billingAddress',
        'phone',
        'accountNumber',
        'ppn',
        'pph',
        'invoiceFormat',
        'nickname',
        'picName',
        'email',
        'npwp',
        'telegramUsername',
        'due_date_duration',
        'companyCode',
        'type',
        'isDo'
    ];

    public function routes()
    {
        return $this->hasMany(Route::class, 'customerCode');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(CustomerDetail::class, 'customerCode', 'code');
    }

    public function pic()
    {
        return $this->hasMany(CustomerPic::class, 'customerCode', 'code');
    }
}
