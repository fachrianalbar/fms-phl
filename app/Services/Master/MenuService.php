<?php

namespace App\Services\Master;

use App\Helpers\GenerateCode;
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

    /**
     * Get all main menus (parentCode = 0)
     */
    public function findAllMainMenu()
    {
        return $this->service->mainMenu()->orderBy('sort')->get();
    }

    /**
     * Get all menus
     */
    public function findAll()
    {
        return $this->service->orderBy('sort')->get();
    }

    /**
     * Get menu by ID
     */
    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    /**
     * Get menu by code
     */
    public function getByCode($code)
    {
        return $this->service->where('code', $code)->first();
    }

    /**
     * Get menu by name
     */
    public function getByName($name)
    {
        return $this->service->where('name', $name)->first();
    }

    /**
     * Get sub menus by parent code
     */
    public function getSubMenus($parentCode)
    {
        return $this->service->subMenuOf($parentCode)->orderBy('sort')->get();
    }

    /**
     * Store new menu
     */
    public function store($request, $title)
    {
        $data = $this->service->create([
            'code' => GenerateCode::generateCode('MN'),
            'name' => $request->name,
            'nama' => $request->nama,
            'parentCode' => $request->parentCode ?? '0',
            'url' => $request->url ?? '#',
            'icon' => $request->icon,
            'sort' => $request->sort ?? 0,
        ]);

        $this->logActivity($title, $data, 'Create');

        return $data;
    }

    /**
     * Update menu
     */
    public function update($request, $id, $title)
    {
        $menu = $this->getById($id);
        $this->logActivity($title, $menu, 'Before Update');

        $updateData = [
            'name' => $request->name,
            'nama' => $request->nama,
            'url' => $request->url ?? '#',
            'icon' => $request->icon,
            'sort' => $request->sort ?? 0,
        ];

        // Only allow parentCode update if it won't break hierarchy
        if (isset($request->parentCode)) {
            $updateData['parentCode'] = $request->parentCode;
        }

        $this->service->where('id', $id)->update($updateData);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    /**
     * Delete menu
     */
    public function destroy($id, $title)
    {
        $menu = $this->getById($id);
        $this->logActivity($title, $menu, 'Delete');

        // Also delete all sub menus
        $this->service->where('parentCode', $menu->code)->delete();

        $this->service->where('id', $id)->delete();
    }

    /**
     * Update parent menu URL based on children
     * If has children, URL should be '#'
     */
    public function updateParentUrl($parentCode)
    {
        $parent = $this->getByCode($parentCode);
        if ($parent && $parent->hasChildren()) {
            $this->service->where('code', $parentCode)->update(['url' => '#']);
        }
    }

    /**
     * Get max sort value for a parent
     */
    public function getMaxSort($parentCode = '0')
    {
        if ($parentCode == '0') {
            return $this->service->mainMenu()->max('sort') ?? 0;
        }

        return $this->service->subMenuOf($parentCode)->max('sort') ?? 0;
    }
}
