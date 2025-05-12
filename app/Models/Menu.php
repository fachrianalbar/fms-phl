<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'menu';
    public $incrementing = false;


    protected $fillable = [
        'code',
        'name',
        'parentCode',
        'url',
        'icon',
        'sort'
    ];
}
