<?php

use App\Http\Controllers\Master\BankReceiverController;
use App\Http\Controllers\Master\BankSenderController;
use App\Http\Controllers\Master\CostComponentController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\DueDateController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\Master\FleetBrandController;
use App\Http\Controllers\Master\FleetController;
use App\Http\Controllers\Master\FleetTypeController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\PositionController;
use App\Http\Controllers\Master\TransactionTypeController;
use App\Http\Controllers\Master\UnitController;
use Illuminate\Support\Facades\Route;


// Route::middleware(['access'])->group(function () {
Route::prefix('master')->name('master.')->group(function () {
    Route::resource('fleets', FleetController::class);
    Route::delete('fleet-picture/{id}', [FleetController::class, 'deleteFleetPicture'])->name('fleet-picture.destroy');
    Route::resource('position', PositionController::class);
    Route::resource('employee', EmployeeController::class);
    Route::resource('fleet-type', FleetTypeController::class);
    Route::resource('fleet-brand', FleetBrandController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('customer', CustomerController::class);
    Route::resource('cost-component', CostComponentController::class);
    Route::resource('location', LocationController::class);
    Route::resource('material', MaterialController::class);
    Route::resource('bank-sender', BankSenderController::class);
    Route::resource('bank-receiver', BankReceiverController::class);
    Route::resource('transaction-type', TransactionTypeController::class);
    Route::resource('due-date', DueDateController::class);
});
// });

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('position', [PositionController::class, 'datatable'])->name('position');
    Route::get('employee', [EmployeeController::class, 'datatable'])->name('employee');
    Route::get('fleets', [FleetController::class, 'datatable'])->name('fleets');
    Route::get('fleet-brand', [FleetBrandController::class, 'datatable'])->name('fleet-brand');
    Route::get('fleet-type', [FleetTypeController::class, 'datatable'])->name('fleet-type');
    Route::get('unit', [UnitController::class, 'datatable'])->name('unit');
    Route::get('customer', [CustomerController::class, 'datatable'])->name('customer');
    Route::get('cost-component', [CostComponentController::class, 'datatable'])->name('cost-component');
    Route::get('location', [LocationController::class, 'datatable'])->name('location');
    Route::get('material', [MaterialController::class, 'datatable'])->name('material');
    Route::get('bank-sender', [BankSenderController::class, 'datatable'])->name('bank-sender');
    Route::get('bank-receiver', [BankReceiverController::class, 'datatable'])->name('bank-receiver');
    Route::get('transaction-type', [TransactionTypeController::class, 'datatable'])->name('transaction-type');
    Route::get('due-date', [DueDateController::class, 'datatable'])->name('due-date');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('city-by-province/{id}', [LocationController::class, 'cityByProvince'])->name('city-by-province');
    Route::get('district-by-city/{id}', [LocationController::class, 'districtByCity'])->name('district-by-city');
});
