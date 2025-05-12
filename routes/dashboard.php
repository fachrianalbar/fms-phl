<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Data\RouteController;
use Illuminate\Support\Facades\Route;


Route::get('home', [DashboardController::class, 'index'])->name('dashboard');


Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('pdf-fleet-maintenance', [DashboardController::class, 'pdfFleetMaintenance'])->name('pdf-fleet-maintenance');
    Route::get('excel-fleet-maintenance', [DashboardController::class, 'excelFleetMaintenance'])->name('excel-fleet-maintenance');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('dashboard-maintenance', [DashboardController::class, 'datatableMaintenance'])->name('dashboard-maintenance');
    Route::get('dashboard-truck-order', [DashboardController::class, 'datatableTruckOrder'])->name('dashboard-truck-order');
});
