<?php

use App\Http\Controllers\Report\AllOrderListController;
use App\Http\Controllers\Report\DriverSalaryController;
use App\Http\Controllers\Report\DriverTonaseController;
use App\Http\Controllers\Report\FleetTonaseController;
use App\Http\Controllers\Report\ProfitLossController;
use Illuminate\Support\Facades\Route;

Route::prefix('report')->name('report.')->group(function () {
    Route::resource('profit-loss', ProfitLossController::class);
    Route::get('excel-profit-loss', [ProfitLossController::class, 'excelProfitLoss'])->name('profit-loss.excel-profit-loss');
    Route::resource('driver-salary', DriverSalaryController::class);
    Route::get('pdf-driver-salary', [DriverSalaryController::class, 'pdfDriverSalary'])->name('driver-salary.pdf-driver-salary');
    Route::resource('driver-tonase', DriverTonaseController::class);
    Route::get('excel-driver-tonase', [DriverTonaseController::class, 'excelDriverTonase'])->name('driver-tonase.excel-driver-tonase');
    Route::resource('fleet-tonase', FleetTonaseController::class);
    Route::get('excel-fleet-tonase', [FleetTonaseController::class, 'excelFleetTonase'])->name('fleet-tonase.excel-fleet-tonase');
    Route::resource('all-order-list', AllOrderListController::class);
    Route::get('excel-all-order-list', [AllOrderListController::class, 'excelAllOrderList'])->name('all-order-list.excel-all-order-list');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('profit-loss', [ProfitLossController::class, 'datatable'])->name('profit-loss');
    Route::get('profit-loss-order', [ProfitLossController::class, 'datatableOrder'])->name('profit-loss-order');
    Route::get('profit-loss-maintenance', [ProfitLossController::class, 'datatableMaintenance'])->name('profit-loss-maintenance');
    Route::get('driver-salary', [DriverSalaryController::class, 'datatable'])->name('driver-salary');
    Route::get('fleet-tonase', [FleetTonaseController::class, 'datatable'])->name('fleet-tonase');
    Route::get('driver-tonase', [DriverTonaseController::class, 'datatable'])->name('driver-tonase');
    Route::get('all-order-list', [AllOrderListController::class, 'datatable'])->name('all-order-list');
});

// Route::prefix('ajax')->name('ajax.')->group(function () {
//     Route::get('purchase-detail/{id}', [ProfitLossController::class, 'getProfitLossSummary'])->name('profit-loss-summary');
// });
