<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TelegramController;
use App\Http\Controllers\API\TestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);

Route::middleware('jwt.verify')->group(function () {
    Route::get('/test', [TestController::class, 'index']);
});
