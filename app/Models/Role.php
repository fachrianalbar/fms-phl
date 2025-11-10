<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'role';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function roleMenus()
    {
        return $this->hasMany(RoleMenu::class, 'roleCode', 'code');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menu', 'roleCode', 'menuCode')
            ->using(RoleMenu::class)->withTimestamps();
    }
}
