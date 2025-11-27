<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'menu';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'nama',
        'parentCode',
        'url',
        'icon',
        'sort',
    ];

    /**
     * Get the parent menu
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parentCode', 'code');
    }

    /**
     * Get the children/sub menus
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parentCode', 'code')->orderBy('sort');
    }

    /**
     * Check if this is a main menu (parentCode = 0)
     */
    public function isMainMenu()
    {
        return $this->parentCode == '0' || $this->parentCode === null;
    }

    /**
     * Check if this menu has sub menus
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Scope for main menus only
     */
    public function scopeMainMenu($query)
    {
        return $query->where('parentCode', '0')->orWhereNull('parentCode');
    }

    /**
     * Scope for sub menus by parent code
     */
    public function scopeSubMenuOf($query, $parentCode)
    {
        return $query->where('parentCode', $parentCode);
    }
}
