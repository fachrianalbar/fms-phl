<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    protected $service;
    protected $title;

    public function __construct(UserService $userSvc, MenuService $menuSvc)
    {
        $this->service = $userSvc;
        $this->title = "Change Password";
    }
    public function index()
    {
        return view('auth.change-password')
            ->with('title', $this->title);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required'],
            'new_password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('change-password.index')->with('fail',  $validator->errors()->all()[0]);
        }

        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return redirect()->route('change-password.index')->with('fail',  "The old password is incorrect!");
        }

        $new_password = Hash::make($request->new_password);
        $this->service->changePassword($new_password);

        return redirect()->route('change-password.index')->with('success', 'Password changed successfully');
    }
}
