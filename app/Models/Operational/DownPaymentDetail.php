<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownPaymentDetail extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'down_payment_detail';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'date',
        'time',
        'price',
        'note',
        'dpCode',
    ];

    public function downPayment()
    {
        return $this->belongsTo(DownPayment::class);
    }
}
