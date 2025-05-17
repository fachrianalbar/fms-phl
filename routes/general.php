<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;




Route::prefix('genaral')->name('general.')->group(function () {
    Route::post('change-languange', [UserController::class, 'changeLanguange'])->name('change-languange');
});
