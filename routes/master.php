<?php

use App\Http\Controllers\Master\BankReceiverController;
use App\Http\Controllers\Master\BankSenderController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\CostComponentController;
use App\Http\Controllers\Master\CostComponentPriceLogController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\DueDateController;
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
    Route::delete('destroy-multiple-fleets', [FleetController::class, 'destroyMultiple'])->name('fleets.destroy-multiple');
    Route::delete('fleet-picture/{id}', [FleetController::class, 'deleteFleetPicture'])->name('fleet-picture.destroy');
    Route::resource('position', PositionController::class);
    Route::resource('employee', EmployeeController::class);
    Route::resource('fleet-type', FleetTypeController::class);
    Route::resource('fleet-brand', FleetBrandController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('company', CompanyController::class);
    Route::resource('customer', CustomerController::class);
    Route::delete('customer-detail/{id}', [CustomerController::class, 'deleteCustomerDetail'])->name('customer-detail.destroy');
    Route::delete('customer-pic/{id}', [CustomerController::class, 'deleteCustomerPic'])->name('customer-pic.destroy');
    Route::resource('cost-component', CostComponentController::class);
    Route::get('cost-component-export/excel', [CostComponentController::class, 'exportExcel'])->name('cost-component.export-excel');
    Route::get('cost-component-price-log', [CostComponentPriceLogController::class, 'index'])->name('cost-component-price-log.index');
    Route::get('cost-component-price-log-export/excel', [CostComponentPriceLogController::class, 'exportExcel'])->name('cost-component-price-log.export-excel');
    Route::resource('location', LocationController::class);
    Route::resource('material', MaterialController::class);
    Route::resource('bank-sender', BankSenderController::class);
    Route::resource('bank-receiver', BankReceiverController::class);
    Route::resource('transaction-type', TransactionTypeController::class);
    Route::resource('due-date', DueDateController::class);
    Route::resource('fleet-company', FleetCompanyController::class);

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
    Route::get('fleet-type', [FleetTypeController::class, 'datatable'])->name('fleet-type');
    Route::get('unit', [UnitController::class, 'datatable'])->name('unit');
    Route::get('company', [CompanyController::class, 'datatable'])->name('company');
    Route::get('customer', [CustomerController::class, 'datatable'])->name('customer');
    Route::get('cost-component', [CostComponentController::class, 'datatable'])->name('cost-component');
    Route::get('cost-component-price-log', [CostComponentPriceLogController::class, 'datatable'])->name('cost-component-price-log');
    Route::get('location', [LocationController::class, 'datatable'])->name('location');
    Route::get('material', [MaterialController::class, 'datatable'])->name('material');
    Route::get('bank-sender', [BankSenderController::class, 'datatable'])->name('bank-sender');
    Route::get('bank-receiver', [BankReceiverController::class, 'datatable'])->name('bank-receiver');
    Route::get('transaction-type', [TransactionTypeController::class, 'datatable'])->name('transaction-type');
    Route::get('due-date', [DueDateController::class, 'datatable'])->name('due-date');
    Route::get('fleet-company', [FleetCompanyController::class, 'datatable'])->name('fleet-company');

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
