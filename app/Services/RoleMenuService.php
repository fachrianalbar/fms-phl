<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Support\Facades\Auth;

class RoleMenuService
{
    protected $service;

    public function __construct(RoleMenu $roleMenu)
    {
        $this->service = $roleMenu;
    }

    public function findMenu()
    {
        return $this->service->where('roleCode', Auth::user()->roleCode)->get()->toArray();
    }
}
