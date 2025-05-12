<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TelegramController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\Operational\OrderMonitoringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('api-key')->group(function () {
    Route::get('order-tracking', [OrderMonitoringController::class, 'orderTracking']);
});

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);



Route::middleware('jwt.verify')->group(function () {
    Route::get('/test', [TestController::class, 'index']);
});
