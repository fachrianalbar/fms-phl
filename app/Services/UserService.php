<?php

namespace App\Services;

use App\Helpers\GenerateCode;
use App\Models\User;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use LogActivity;

    protected $service;

    public function __construct(User $user)
    {
        $this->service = $user;
    }

    public function findAll()
    {
        return $this->service->with(['role'])->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $data = $this->service->create([
            'name' => $request->name,
            'username' => $request->username,
            'roleCode' => $request->roleCode,
            'password' =>  Hash::make('123456'),
            'code' => GenerateCode::generateCode('TUSR')
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'username' => $request->username,
            'roleCode' => $request->roleCode,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }

    public function changePassword($newPassword)
    {
        $this->service->where('id', Auth::user()->id)->update([
            'password' => $newPassword
        ]);
    }

    public function reset($id)
    {
        $this->service->where('id', $id)->update([
            'password' => Hash::make('123456')
        ]);
    }
}
