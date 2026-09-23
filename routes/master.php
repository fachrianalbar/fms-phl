<?php

use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\CostComponentController;
use App\Http\Controllers\Master\CostComponentPriceLogController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\Master\FleetBrandController;
use App\Http\Controllers\Master\FleetCompanyController;
use App\Http\Controllers\Master\FleetController;
use App\Http\Controllers\Master\FleetTypeController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\MenuController;
use App\Http\Controllers\Master\PositionController;
use App\Http\Controllers\Master\TransactionTypeController;
use App\Http\Controllers\Master\UnitController;
use Illuminate\Support\Facades\Route;

// Route::middleware(['access'])->group(function () {
Route::prefix('master')->name('master.')->group(function () {
    Route::resource('fleets', FleetController::class);
    Route::get('fleets-export/excel', [FleetController::class, 'exportExcel'])->name('fleets.export-excel');
    Route::get('fleets-export/pdf', [FleetController::class, 'exportPdf'])->name('fleets.export-pdf');
    Route::get('fleets/{id}/export/pdf', [FleetController::class, 'exportDetailPdf'])->name('fleets.detail-export-pdf');
    Route::delete('destroy-multiple-fleets', [FleetController::class, 'destroyMultiple'])->name('fleets.destroy-multiple');
    Route::delete('fleet-picture/{id}', [FleetController::class, 'deleteFleetPicture'])->name('fleet-picture.destroy');
    Route::resource('position', PositionController::class);
    Route::resource('employee', EmployeeController::class);
    Route::post('employee/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employee.toggle-status');
    Route::resource('fleet-type', FleetTypeController::class);
    Route::get('fleet-type-export/excel', [FleetTypeController::class, 'exportExcel'])->name('fleet-type.export-excel');
    Route::get('fleet-type-export/pdf', [FleetTypeController::class, 'exportPdf'])->name('fleet-type.export-pdf');
    Route::get('fleet-type/{id}/export/excel', [FleetTypeController::class, 'exportDetailExcel'])->name('fleet-type.detail-export-excel');
    Route::get('fleet-type/{id}/export/pdf', [FleetTypeController::class, 'exportDetailPdf'])->name('fleet-type.detail-export-pdf');
    Route::resource('fleet-brand', FleetBrandController::class);
    Route::get('fleet-brand-export/excel', [FleetBrandController::class, 'exportExcel'])->name('fleet-brand.export-excel');
    Route::get('fleet-brand-export/pdf', [FleetBrandController::class, 'exportPdf'])->name('fleet-brand.export-pdf');
    Route::get('fleet-brand/{id}/export/excel', [FleetBrandController::class, 'exportDetailExcel'])->name('fleet-brand.detail-export-excel');
    Route::get('fleet-brand/{id}/export/pdf', [FleetBrandController::class, 'exportDetailPdf'])->name('fleet-brand.detail-export-pdf');
    Route::resource('unit', UnitController::class);
    Route::resource('company', CompanyController::class);
    Route::resource('customer', CustomerController::class);
    Route::get('customer-export/excel', [CustomerController::class, 'exportExcel'])->name('customer.export-excel');
    Route::get('customer-export/pdf', [CustomerController::class, 'exportPdf'])->name('customer.export-pdf');
    Route::delete('customer-detail/{id}', [CustomerController::class, 'deleteCustomerDetail'])->name('customer-detail.destroy');
    Route::delete('customer-pic/{id}', [CustomerController::class, 'deleteCustomerPic'])->name('customer-pic.destroy');
    Route::resource('cost-component', CostComponentController::class);
    Route::get('cost-component-export/excel', [CostComponentController::class, 'exportExcel'])->name('cost-component.export-excel');
    Route::get('cost-component-price-log', [CostComponentPriceLogController::class, 'index'])->name('cost-component-price-log.index');
    Route::get('cost-component-price-log-export/excel', [CostComponentPriceLogController::class, 'exportExcel'])->name('cost-component-price-log.export-excel');
    Route::resource('location', LocationController::class);
    Route::resource('material', MaterialController::class);
    Route::resource('transaction-type', TransactionTypeController::class);
    Route::resource('fleet-company', FleetCompanyController::class);
    Route::get('fleet-company-export/excel', [FleetCompanyController::class, 'exportExcel'])->name('fleet-company.export-excel');
    Route::get('fleet-company-export/pdf', [FleetCompanyController::class, 'exportPdf'])->name('fleet-company.export-pdf');
    Route::get('fleet-company/{id}/export/excel', [FleetCompanyController::class, 'exportDetailExcel'])->name('fleet-company.detail-export-excel');
    Route::get('fleet-company/{id}/export/pdf', [FleetCompanyController::class, 'exportDetailPdf'])->name('fleet-company.detail-export-pdf');

    // Menu routes
    Route::resource('menu', MenuController::class);
    Route::get('menu/sub-menu/{parentCode}', [MenuController::class, 'subMenu'])->name('menu.sub-menu');
    Route::get('menu/create-sub-menu/{parentCode}', [MenuController::class, 'createSubMenu'])->name('menu.create-sub-menu');
});
// });
Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('position', [PositionController::class, 'datatable'])->name('position');
    Route::get('employee', [EmployeeController::class, 'datatable'])->name('employee');
    Route::get('fleets', [FleetController::class, 'datatable'])->name('fleets');
    Route::get('fleet-brand', [FleetBrandController::class, 'datatable'])->name('fleet-brand');
    Route::get('fleet-brand/{id}/fleets', [FleetBrandController::class, 'datatableFleets'])->name('fleet-brand.fleets');
    Route::get('fleet-type', [FleetTypeController::class, 'datatable'])->name('fleet-type');
    Route::get('fleet-type/{id}/fleets', [FleetTypeController::class, 'datatableFleets'])->name('fleet-type.fleets');
    Route::get('unit', [UnitController::class, 'datatable'])->name('unit');
    Route::get('company', [CompanyController::class, 'datatable'])->name('company');
    Route::get('customer', [CustomerController::class, 'datatable'])->name('customer');
    Route::get('cost-component', [CostComponentController::class, 'datatable'])->name('cost-component');
    Route::get('cost-component-price-log', [CostComponentPriceLogController::class, 'datatable'])->name('cost-component-price-log');
    Route::get('location', [LocationController::class, 'datatable'])->name('location');
    Route::get('material', [MaterialController::class, 'datatable'])->name('material');
    Route::get('transaction-type', [TransactionTypeController::class, 'datatable'])->name('transaction-type');
    Route::get('fleet-company', [FleetCompanyController::class, 'datatable'])->name('fleet-company');
    Route::get('fleet-company/{id}/fleets', [FleetCompanyController::class, 'datatableFleets'])->name('fleet-company.fleets');

    // Menu datatables
    Route::get('menu', [MenuController::class, 'datatable'])->name('menu');
    Route::get('menu-sub/{parentCode}', [MenuController::class, 'datatableSubMenu'])->name('menu-sub');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('city-by-province/{id}', [LocationController::class, 'cityByProvince'])->name('city-by-province');
    Route::get('district-by-city/{id}', [LocationController::class, 'districtByCity'])->name('district-by-city');
    Route::get('customer-detail/{customerId}', [CustomerController::class, 'customerDetail'])->name('customer-detail');
    Route::get('fleet-driver/{code}', [FleetController::class, 'fleetDriver'])->name('fleet-driver');
    Route::get('customer-company-format/{code}', [CustomerController::class, 'customerCompanyFormat'])->name('customer-company-format');
    Route::get('cost-components/all', [CostComponentController::class, 'getAllCostComponents'])->name('cost-components-all');
    Route::post('cost-component/bulk-update-price', [CostComponentController::class, 'bulkUpdatePrice'])->name('cost-component.bulk-update-price');
});
