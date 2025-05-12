<?php

namespace App\Services;

use App\Helpers\GenerateCode;
use App\Models\Role;
use App\Models\RoleMenu;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    use LogActivity;

    protected $service;

    public function __construct(Role $role)
    {
        $this->service = $role;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'code' => GenerateCode::generateCode('TRL')
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }

    public function roleAccess($selectedMenus, $menuStructure, $id)
    {
        $data = $this->getById($id);
        foreach ($menuStructure as $header => $items) {
            $hasCheckedItems = false;

            foreach ($items as $item) {
                if (in_array($item, $selectedMenus)) {
                    // Check if item already exists in the role_menu table
                    $roleMenu = RoleMenu::where('menuCode', $item)->where('roleCode', $data->code)->first();


                    if (!$roleMenu) {
                        // Insert the item if it doesn't exist
                        RoleMenu::create([
                            'code' => GenerateCode::generateCode('TRL'),
                            'roleCode' => $data->code,
                            'menuCode' => $item,
                        ]);
                    }
                    $hasCheckedItems = true;
                } else {
                    // If unchecked, delete the item if it exists
                    RoleMenu::where('menuCode', $item)->where('roleCode', $data->code)->delete();
                }
            }

            if ($hasCheckedItems) {
                // Ensure the header is present if at least one child item is checked
                $headerRoleMenu = RoleMenu::where('menuCode', $header)->where('roleCode', $data->code)->first();

                if (!$headerRoleMenu) {
                    RoleMenu::create([
                        'code' => GenerateCode::generateCode('TRL'),
                        'roleCode' => $data->code,
                        'menuCode' => $header,
                    ]);
                }
            } else {
                // Delete the header if no child items are checked
                RoleMenu::where('menuCode', $header)->where('roleCode', $data->code)->delete();
            }
        }
    }

    public function findMenu()
    {
        return $this->service->where('code', Auth::user()->roleCode)->first();
    }

    public function findMenuById($id)
    {
        return $this->service->find($id)->menus()->orderBy('sort', 'asc')->get();
    }
}
