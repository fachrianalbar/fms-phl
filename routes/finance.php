<?php

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\OrderPaymentController;
use App\Http\Controllers\Finance\VendorPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')->name('finance.')->group(function () {
    Route::resource('invoice-payment', InvoicePaymentController::class);
    Route::resource('invoice', InvoiceController::class);
    Route::post('vendor-payment/generate-nota', [VendorPaymentController::class, 'generateNota'])->name('vendor-payment.generate-nota');
    Route::post('vendor-payment/cancel-nota/{orderCode}', [VendorPaymentController::class, 'cancelNota'])->name('vendor-payment.cancel-nota');
    Route::resource('vendor-payment', VendorPaymentController::class);
    Route::resource('order-payment', OrderPaymentController::class);
    Route::put('invoice-detail/{id}', [InvoiceController::class, 'storeInvoiceDetail'])->name('invoice-detail.store');
    Route::delete('invoice-detail/{id}', [InvoiceController::class, 'destroyInvoiceDetail'])->name('invoice-detail.destroy');
    Route::get('pdf-invoice/{id}', [InvoiceController::class, 'pdfInvoice'])->name('invoice.pdf-invoice');
    Route::get('pdf-vendor-payment/{orderCode}', [VendorPaymentController::class, 'pdfVendorPayment'])->name('vendor-payment.pdf');
    Route::post('pdf-vendor-payment-multi', [VendorPaymentController::class, 'pdfVendorPaymentMulti'])->name('vendor-payment.pdf-multi');
    Route::post('pdf-order-payment-multi', [OrderPaymentController::class, 'pdfOrderPaymentMulti'])->name('order-payment.pdf-multi');
    Route::post('invoice/{id}/payment', [InvoiceController::class, 'processPayment'])->name('invoice.process-payment');
    Route::post('invoice/{id}/recalculate', [InvoiceController::class, 'recalculate'])->name('invoice.recalculate');
    Route::get('invoice-payment/export/pdf', [InvoicePaymentController::class, 'exportPdf'])->name('invoice-payment.export-pdf');
    Route::get('invoice-payment/export/excel', [InvoicePaymentController::class, 'exportExcel'])->name('invoice-payment.export-excel');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('invoice', [InvoiceController::class, 'datatable'])->name('invoice');
    Route::get('invoice-payment', [InvoicePaymentController::class, 'datatable'])->name('invoice-payment');
    Route::get('vendor-payment', [VendorPaymentController::class, 'datatable'])->name('vendor-payment');
    Route::get('order-payment', [OrderPaymentController::class, 'datatable'])->name('order-payment');
    Route::get('invoice-order', [InvoiceController::class, 'datatableOrder'])->name('invoice-order');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('customer-invoice/{customerCode}', [InvoiceController::class, 'customerInvoice'])->name('customer-finance');
    Route::get('invoice-number-format/{id}', [InvoiceController::class, 'invoiceNumberFormat'])->name('invoice-number-format');
    Route::get('order-detail-payment/{orderCode}', [OrderPaymentController::class, 'orderDetailPayment'])->name('order-detail-payment');
    Route::get('vendor-payment-detail/{orderCode}', [VendorPaymentController::class, 'getDetail'])->name('vendor-payment-detail');
});
