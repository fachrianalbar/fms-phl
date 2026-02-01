<?php

use App\Http\Controllers\Operational\BonUjtController;
use App\Http\Controllers\Operational\DownPaymentController;
use App\Http\Controllers\Operational\DownPaymentDetailController;
use App\Http\Controllers\Operational\NotReturnDoController;
use App\Http\Controllers\Operational\OrderController;
use App\Http\Controllers\Operational\OrderMonitoringController;
use App\Http\Controllers\Operational\OrderOfficeController;
use App\Http\Controllers\Operational\OrderTaxController;
use App\Http\Controllers\Operational\ReturnDoController;
use Illuminate\Support\Facades\Route;

Route::prefix('operational')->name('operational.')->group(function () {
    Route::resource('down-payment', DownPaymentController::class);
    Route::resource('down-payment-detail', DownPaymentDetailController::class);
    Route::resource('monitoring-order', OrderMonitoringController::class);
    Route::resource('return-do', ReturnDoController::class);
    Route::get('return-do/{orderId}/files', [ReturnDoController::class, 'getOrderFiles'])->name('return-do.get-files');
    Route::resource('not-return-do', NotReturnDoController::class)->except(['update']);
    Route::resource('order-tax', OrderTaxController::class);
    Route::post('confirm-do', [NotReturnDoController::class, 'confirmDo'])->name('not-return-do.confirm-do');
    Route::put('not-return-do/update/{code}', [NotReturnDoController::class, 'update'])->name('not-return-do.update');
    Route::put('not-return-do/confirm/{code}', [NotReturnDoController::class, 'confirmReturn'])->name('not-return-do.confirm-return');
    Route::post('not-return-do/{code}/upload-surat-jalan', [NotReturnDoController::class, 'uploadSuratJalan'])->name('not-return-do.upload-surat-jalan');
    Route::get('not-return-do-edit/{code}/edit', [NotReturnDoController::class, 'editOrder'])->name('not-return-do.edit-order');
    Route::put('not-return-do-edit/{code}', [NotReturnDoController::class, 'updateOrder'])->name('not-return-do.update-order');
    Route::post('cancel-do', [ReturnDoController::class, 'cancelDo'])->name('return-do.cancel-do');
    Route::put('finish-order/{id}', [OrderController::class, 'finishOrder'])->name('finish-order');
    Route::post('order-driver', [OrderController::class, 'storeOrderDriver'])->name('store-order-driver');
    Route::get('order-drivers', [OrderController::class, 'getOrderDrivers'])->name('order.get-order-drivers');
    Route::delete('order-driver', [OrderController::class, 'deleteOrderDriver'])->name('order.delete-order-driver');
    Route::post('order-cost', [OrderController::class, 'storeOrderCost'])->name('order.store-order-cost');
    Route::get('order-costs', [OrderController::class, 'getOrderCosts'])->name('order.get-order-costs');
    Route::delete('order-cost', [OrderController::class, 'deleteOrderCost'])->name('order.delete-order-cost');

    // (commented duplicate removed)
    Route::get('pdf-down-payment/{id}', [DownPaymentController::class, 'pdfDownPayment'])->name('down-payment.pdf-down-payment');
    Route::resource('bon-ujt', BonUjtController::class);
    Route::put('bon-ujt-detail/{id}', [BonUjtController::class, 'storeBonUjtDetail'])->name('bon-ujt-detail.store');
    Route::delete('bon-ujt-detail/{id}', [BonUjtController::class, 'destroyBonUjtDetail'])->name('bon-ujt-detail.destroy');
    Route::resource('order', OrderController::class);
    Route::post('store-order-tax', [OrderController::class, 'storeOrderTax'])->name('order.store-order-tax');
    Route::get('/order/{id}/detail', [OrderController::class, 'showOrder'])->name('order.show-order');
    Route::get('check-order-null-relation', [OrderController::class, 'checkNullRelations'])->name('order.check-null-relation');
    Route::get('excel-order', [OrderController::class, 'excelOrder'])->name('order.excel-order');
    Route::resource('office-order', OrderOfficeController::class);
    Route::get('pdf-bon-ujt/{id}', [BonUjtController::class, 'pdfBonUjt'])->name('bon-ujt.pdf-bon-ujt');
    Route::delete('order-cost/{id}', [OrderController::class, 'deleteCost'])->name('order-cost.destroy');
    Route::delete('order-material/{id}', [OrderController::class, 'deleteOrderMaterial'])->name('order-material.destroy');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('down-payment', [DownPaymentController::class, 'datatable'])->name('down-payment');
    Route::get('down-payment-detail', [DownPaymentController::class, 'datatableDetail'])->name('down-payment-detail');
    Route::get('order', [OrderController::class, 'datatable'])->name('order');
    Route::get('office-order', [OrderOfficeController::class, 'datatable'])->name('office-order');
    Route::get('bon-ujt', [BonUjtController::class, 'datatable'])->name('bon-ujt');
    Route::get('bon-ujt-order', [BonUjtController::class, 'datatableOrder'])->name('bon-ujt-order');
    Route::get('bon-ujt-order-detail', [BonUjtController::class, 'datatableOrderDetail'])->name('bon-ujt-order-detail');
    Route::get('order-monitoring', [OrderMonitoringController::class, 'datatable'])->name('order-monitoring');
    Route::get('not-return-do', [NotReturnDoController::class, 'datatable'])->name('not-return-do');
    Route::get('return-do', [ReturnDoController::class, 'datatable'])->name('return-do');
    Route::get('order-tax', [OrderTaxController::class, 'datatable'])->name('order-tax');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('route-order/{customerId}/{routeTypeCode}', [OrderController::class, 'routeOrder'])->name('route-order');
    Route::get('origin-by-customer/{customerCode}/{routeTypeCode}', [OrderController::class, 'originCustomer'])->name('origin-by-customer');
    Route::get('destination-by-customer/{customerCode}/{routeTypeCode}/{originLocationCode}', [OrderController::class, 'destinationCustomer'])->name('destination-by-customer');
    Route::get('route-order-detail/{routeCode}', [OrderController::class, 'routeOrderDetail'])->name('route-order-detail');
    Route::get('down-payment-data/{id}', [DownPaymentController::class, 'data'])->name('down-payment-data');
    Route::get('order-generate-code', [OrderController::class, 'generateCode'])->name('order-generate-code');
    Route::get('order-shipment-format/{id}', [OrderController::class, 'shipmentFormat'])->name('order-shipment-format');
    Route::post('order-calculate-price', [OrderController::class, 'calculateOrderPrice'])->name('order-calculate-price');
});
