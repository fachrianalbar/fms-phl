<?php

namespace App\Models\Operational;

use App\Models\Master\Employee;
use App\Models\User;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownPayment extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'down_payment';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'pic',
        'price',
        'note',
        'date',
        'time',
        'driverCode',
        'type'
    ];

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driverCode', 'code');
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic', 'id');
    }

    public function details()
    {
        return $this->hasMany(DownPaymentDetail::class, 'dpCode', 'code');
    }
}
