<?php

use App\Http\Controllers\Report\AllOrderListController;
use App\Http\Controllers\Report\DriverSalaryController;
use App\Http\Controllers\Report\DriverTonaseController;
use App\Http\Controllers\Report\FleetTonaseController;
use App\Http\Controllers\Report\MaintenancePerCompanyController;
use App\Http\Controllers\Report\MaintenancePerFleetController;
use App\Http\Controllers\Report\OrderDetailController;
use App\Http\Controllers\Report\ProfitLossController;
use App\Http\Controllers\Report\SupplierPurchaseController;
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
    Route::resource('order-detail', OrderDetailController::class);
    Route::get('excel-order-detail', [OrderDetailController::class, 'excelOrderDetail'])->name('order-detail.excel-order-detail');
    Route::get('pdf-order-detail', [OrderDetailController::class, 'pdfOrderDetail'])->name('order-detail.pdf-order-detail');
    Route::get('maintenance-fleet', [MaintenancePerFleetController::class, 'index'])->name('maintenance-fleet.index');
    Route::get('maintenance-fleet/detail/{fleetCode}', [MaintenancePerFleetController::class, 'detail'])->name('maintenance-fleet.detail');
    Route::get('excel-maintenance-fleet', [MaintenancePerFleetController::class, 'excelMaintenanceFleet'])->name('maintenance-fleet.excel-maintenance-fleet');
    Route::get('pdf-maintenance-fleet', [MaintenancePerFleetController::class, 'pdfMaintenanceFleet'])->name('maintenance-fleet.pdf-maintenance-fleet');
    Route::get('excel-maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'excelMaintenanceFleetDetail'])->name('maintenance-fleet.excel-maintenance-fleet-detail');
    Route::get('pdf-maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'pdfMaintenanceFleetDetail'])->name('maintenance-fleet.pdf-maintenance-fleet-detail');
    Route::get('maintenance-company-internal', [MaintenancePerCompanyController::class, 'index'])->name('maintenance-company-internal.index');
    Route::get('maintenance-company-internal/detail/{fleetCompanyCode}', [MaintenancePerCompanyController::class, 'detail'])->name('maintenance-company-internal.detail');
    Route::get('supplier', [SupplierPurchaseController::class, 'index'])->name('supplier.index');
    Route::get('supplier/detail/{supplierCode}', [SupplierPurchaseController::class, 'detail'])->name('supplier.detail');
    Route::get('excel-supplier', [SupplierPurchaseController::class, 'excelSupplier'])->name('supplier.excel-supplier');
    Route::get('pdf-supplier', [SupplierPurchaseController::class, 'pdfSupplier'])->name('supplier.pdf-supplier');
    Route::get('excel-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'excelSupplierDetail'])->name('supplier.excel-supplier-detail');
    Route::get('pdf-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'pdfSupplierDetail'])->name('supplier.pdf-supplier-detail');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('profit-loss', [ProfitLossController::class, 'datatable'])->name('profit-loss');
    Route::get('profit-loss-order', [ProfitLossController::class, 'datatableOrder'])->name('profit-loss-order');
    Route::get('profit-loss-maintenance', [ProfitLossController::class, 'datatableMaintenance'])->name('profit-loss-maintenance');
    Route::get('driver-salary', [DriverSalaryController::class, 'datatable'])->name('driver-salary');
    Route::get('fleet-tonase', [FleetTonaseController::class, 'datatable'])->name('fleet-tonase');
    Route::get('driver-tonase', [DriverTonaseController::class, 'datatable'])->name('driver-tonase');
    Route::get('all-order-list', [AllOrderListController::class, 'datatable'])->name('all-order-list');
    Route::get('order-detail', [OrderDetailController::class, 'datatable'])->name('order-detail');
    Route::get('maintenance-fleet', [MaintenancePerFleetController::class, 'datatable'])->name('maintenance-fleet');
    Route::get('maintenance-fleet-detail/{fleetCode}', [MaintenancePerFleetController::class, 'datatableDetail'])->name('maintenance-fleet-detail');
    Route::get('maintenance-company-internal', [MaintenancePerCompanyController::class, 'datatable'])->name('maintenance-company-internal');
    Route::get('maintenance-company-internal-detail/{fleetCompanyCode}', [MaintenancePerCompanyController::class, 'datatableDetail'])->name('maintenance-company-internal-detail');
    Route::get('report-supplier', [SupplierPurchaseController::class, 'datatable'])->name('report-supplier');
    Route::get('report-supplier-detail/{supplierCode}', [SupplierPurchaseController::class, 'datatableDetail'])->name('report-supplier-detail');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('profit-loss-summary/{id}', [ProfitLossController::class, 'getProfitLossSummary'])->name('profit-loss-summary');
    Route::get('maintenance-fleet-detail-items/{maintenanceCode}', [MaintenancePerFleetController::class, 'detailItems'])->name('maintenance-fleet-detail-items');
    Route::get('maintenance-company-internal-detail-items/{maintenanceCode}', [MaintenancePerCompanyController::class, 'detailItems'])->name('maintenance-company-internal-detail-items');
    Route::get('supplier-purchase-detail-items/{purchaseCode}', [SupplierPurchaseController::class, 'detailItems'])->name('supplier-purchase-detail-items');
});
