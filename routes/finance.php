<?php

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\VendorPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')->name('finance.')->group(function () {
    Route::resource('invoice-payment', InvoicePaymentController::class);
    Route::resource('invoice', InvoiceController::class);
    Route::resource('vendor-payment', VendorPaymentController::class);
    Route::put('invoice-detail/{id}', [InvoiceController::class, 'storeInvoiceDetail'])->name('invoice-detail.store');
    Route::delete('invoice-detail/{id}', [InvoiceController::class, 'destroyInvoiceDetail'])->name('invoice-detail.destroy');
    Route::get('pdf-invoice/{id}', [InvoiceController::class, 'pdfInvoice'])->name('invoice.pdf-invoice');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('invoice', [InvoiceController::class, 'datatable'])->name('invoice');
    Route::get('invoice-payment', [InvoicePaymentController::class, 'datatable'])->name('invoice-payment');
    Route::get('vendor-payment', [VendorPaymentController::class, 'datatable'])->name('vendor-payment');
    Route::get('invoice-order', [InvoiceController::class, 'datatableOrder'])->name('invoice-order');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('customer-invoice/{customerCode}', [InvoiceController::class, 'customerInvoice'])->name('customer-finance');
});
