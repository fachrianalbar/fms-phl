<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanySetting extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'company_setting';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'address',
        'owner',
        'logo',
        'email',
        'phone'
    ];
}
