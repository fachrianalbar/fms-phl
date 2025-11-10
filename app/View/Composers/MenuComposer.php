<?php

namespace App\View\Composers;

use App\Models\Menu;
use App\Services\RoleMenuService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MenuComposer
{
    protected $service;

    public function __construct(RoleMenuService $roleMenu)
    {
        $this->service = $roleMenu;
    }

    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        // if ($view->getName() == 'layouts.sidebar') {
        //     $menus = Menu::OrderBy('sort', 'asc')->get();
        //     $view->with('menus', $menus);
        // }

        // if ($view->getName() == 'layouts.sidebar') {
        //     $view->with('menus', $this->service->findMenuById(Auth::user()->role->code));
        // }

        if ($view->getName() == 'layouts.sidebar') {
            $roleMenu = $this->service->findMenu();
            $menus = Menu::whereIn('code', array_column($roleMenu, 'menuCode'))->OrderBy('sort', 'asc')->get();
            $view->with('menus', $menus);
        }
    }
}
