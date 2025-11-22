<?php

namespace App\Services\Master;

use App\Models\Menu;
use App\Traits\LogActivity;

class MenuService
{
    use LogActivity;

    protected $service;

    public function __construct(Menu $menu)
    {
        $this->service = $menu;
    }

    public function getByName($name)
    {
        return $this->service->where('name', $name)->first();
    }
}
