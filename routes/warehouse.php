<?php

use App\Http\Controllers\Warehouse\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('warehouse')->name('warehouse.')->group(function () {
    Route::resource('maintenance', MaintenanceController::class);
    Route::delete('maintenance-detail/{id}', [MaintenanceController::class, 'deleteMaintenanceDetail'])->name('maintenance-detail.destroy');
    Route::get('pdf-maintenance', [MaintenanceController::class, 'pdfMaintenance'])->name('maintenance.pdf-maintenance');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('maintenance', [MaintenanceController::class, 'datatable'])->name('maintenance');
});
