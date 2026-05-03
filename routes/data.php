<?php

use App\Http\Controllers\Data\DropLocationController;
use App\Http\Controllers\Data\FleetDriverController;
use App\Http\Controllers\Data\PickupLocationController;
use App\Http\Controllers\Data\RouteController;
use App\Http\Controllers\Data\RouteDetailController;
use App\Http\Controllers\Data\TonaseBonusController;
use Illuminate\Support\Facades\Route;

Route::prefix('data')->name('data.')->group(function () {
    Route::resource('fleet-owner', FleetDriverController::class);
    Route::resource('drop-location', DropLocationController::class);
    Route::resource('pickup-location', PickupLocationController::class);
    Route::resource('route', RouteController::class);
    Route::resource('tonase-bonus', TonaseBonusController::class);
    Route::resource('route-detail', RouteDetailController::class);
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('fleet-owner', [FleetDriverController::class, 'datatable'])->name('fleet-owner');
    Route::get('drop-location', [DropLocationController::class, 'datatable'])->name('drop-location');
    Route::get('pickup-location', [PickupLocationController::class, 'datatable'])->name('pickup-location');
    Route::get('tonase-bonus', [TonaseBonusController::class, 'datatable'])->name('tonase-bonus');
    Route::get('route', [RouteController::class, 'datatable'])->name('route');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('location-by-customer/{code}', [RouteController::class, 'locationByCustomer'])->name('location-by-customer');
    Route::get('cost-component-price/{code}', [RouteController::class, 'getCostComponentPrice'])->name('cost-component-price');
    Route::post('route/bulk-update-price', [RouteController::class, 'bulkUpdatePrice'])->name('route.bulk-update-price');
    Route::post('route/bulk-delete', [RouteController::class, 'bulkDestroy'])->name('route.bulk-delete');
    Route::get('route/{id}/vendor-prices', [RouteController::class, 'showVendorPrices'])->name('route.vendor-prices.show');
    Route::post('route/{id}/vendor-prices', [RouteController::class, 'saveVendorPrices'])->name('route.vendor-prices.save');
});
