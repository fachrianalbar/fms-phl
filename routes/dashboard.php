<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Data\RouteController;
use Illuminate\Support\Facades\Route;


Route::get('home', [DashboardController::class, 'index'])->name('dashboard');


Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('pdf-fleet-maintenance', [DashboardController::class, 'pdfFleetMaintenance'])->name('pdf-fleet-maintenance');
    Route::get('excel-fleet-maintenance', [DashboardController::class, 'excelFleetMaintenance'])->name('excel-fleet-maintenance');

    // API routes untuk dashboard
    Route::get('customer-stats', [DashboardController::class, 'getCustomerStats'])->name('customer-stats');
    Route::get('fleet-stats', [DashboardController::class, 'getFleetStats'])->name('fleet-stats');
    Route::get('order-stats-year', [DashboardController::class, 'getOrderStatsByYear'])->name('order-stats-year');
    Route::get('order-count', [DashboardController::class, 'getOrderCount'])->name('order-count');
    Route::get('pending-invoice-orders', [DashboardController::class, 'getPendingInvoiceOrders'])->name('pending-invoice-orders');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('dashboard-maintenance', [DashboardController::class, 'datatableMaintenance'])->name('dashboard-maintenance');
    Route::get('dashboard-truck-order', [DashboardController::class, 'datatableTruckOrder'])->name('dashboard-truck-order');
});
