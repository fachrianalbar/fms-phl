<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    use HasFactory, Uuid;

    protected $table = 'role_menu';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'roleCode',
        'menuCode',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menuCode', 'code');
    }
}
