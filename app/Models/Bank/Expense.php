<?php

namespace App\Models\Bank;

use App\Models\Mutation;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'expense';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'driverCode',
        'mutationCode',
        'createdBy',
    ];

    public function mutation()
    {
        return $this->belongsTo(Mutation::class, 'mutationCode', 'code');
    }
}
