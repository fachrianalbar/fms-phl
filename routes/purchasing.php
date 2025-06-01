<?php

use App\Http\Controllers\Purchasing\PurchaseConfirmationController;
use App\Http\Controllers\Purchasing\PurchaseController;
use App\Http\Controllers\Purchasing\PurchasePaymentController;
use App\Http\Controllers\Purchasing\PurchaseVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchasing')->name('purchasing.')->group(function () {
    Route::resource('purchase', PurchaseController::class);
    Route::resource('purchase-verification', PurchaseVerificationController::class);
    Route::resource('purchase-confirmation', PurchaseConfirmationController::class);
    Route::resource('purchase-payment', PurchasePaymentController::class);

    Route::delete('purchase-detail/{id}', [PurchaseController::class, 'deletePurchaseDetail'])->name('purchase-detail.destroy');
    Route::delete('purchase-verification-detail/{id}', [PurchaseVerificationController::class, 'deletePurchaseDetail'])->name('purchase-verification-detail.destroy');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('purchase', [PurchaseController::class, 'datatable'])->name('purchase');
    Route::get('purchase-verification', [PurchaseVerificationController::class, 'datatable'])->name('purchase-verification');
    Route::get('purchase-confirmation', [PurchaseConfirmationController::class, 'datatable'])->name('purchase-confirmation');
    Route::get('purchase-payment', [PurchasePaymentController::class, 'datatable'])->name('purchase-payment');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('item-by-supplier/{supplierCode}', [PurchaseController::class, 'itemBySupplier'])->name('item-by-supplier');
    Route::get('purchase-detail/{id}', [PurchaseConfirmationController::class, 'purchaseDetail'])->name('purchase-detail');
    Route::get('purchase-generate-code', [PurchaseController::class, 'generateCode'])->name('purchase-generate-code');
});
