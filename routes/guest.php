<?php

use App\Http\Controllers\GuestOrderMonitoringController;
use Illuminate\Support\Facades\Route;

Route::name('guest.')->group(function () {
    Route::get('order-track', [GuestOrderMonitoringController::class, 'index'])->name('order-track');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('guest-order-shipment/{shipment}', [GuestOrderMonitoringController::class, 'guestorderShipment'])->name('guest-order-shipment');
    Route::get('guest-order-shipment-suggestion', [GuestOrderMonitoringController::class, 'guestorderShipmentSuggestion'])->name('guest-order-shipment-suggestion');
});
