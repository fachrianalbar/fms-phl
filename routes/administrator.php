<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['access:SPRADMIN'])->group(function () {
    Route::prefix('administrator')->name('administrator.')->group(function () {
        Route::resource('role', RoleController::class);
        Route::put('role-access/{id}', [RoleController::class, 'roleAccess'])->name('role-access');
        Route::resource('user', UserController::class);
        Route::put('user-reset/{id}', [UserController::class, 'reset'])->name('user-reset');
        Route::resource('company-setting', CompanySettingController::class);
        Route::resource('activity-log', ActivityLogController::class)->only(['index']);
    });

    Route::prefix('datatable')->name('dt.')->group(function () {
        Route::get('role', [RoleController::class, 'datatable'])->name('role');
        Route::get('user', [UserController::class, 'datatable'])->name('user');
        Route::get('company-setting', [CompanySettingController::class, 'datatable'])->name('company-setting');
        Route::get('activity-log', [ActivityLogController::class, 'datatable'])->name('activity-log');
    });
});
