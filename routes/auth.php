<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use Illuminate\Support\Facades\Route;

Route::resource('change-password', ChangePasswordController::class);
Route::get('/', [AuthController::class, 'login'])->middleware('guest')->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
