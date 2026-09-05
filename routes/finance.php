<?php

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\OrderPaymentController;
use App\Http\Controllers\Finance\VendorInvoiceController;
use Illuminate\Support\Facades\Route;
Route::prefix('finance')->name('finance.')->group(function () {
    // Vendor Payment kini berada di menu Vendor (routes/vendor.php).
    // URL lama dialihkan ke halaman baru agar bookmark tetap jalan.
    Route::get('vendor-payment', fn () => redirect()->route('vendor.invoice.unpaid'));
    Route::resource('order-payment', OrderPaymentController::class);
    Route::get('pdf-vendor-payment/{orderCode}', [VendorInvoiceController::class, 'pdf'])->name('vendor-payment.pdf');
    Route::post('pdf-order-payment-multi', [OrderPaymentController::class, 'pdfOrderPaymentMulti'])->name('order-payment.pdf-multi');

    // Fallback redirect dari route lama finance/invoice ke route baru invoice
    Route::get('invoice', fn () => redirect()->route('invoice.unpaid'));
    Route::get('invoice/create', fn () => redirect()->route('invoice.create'));
    Route::get('invoice-payment', fn () => redirect()->route('invoice.payment.index'));
    Route::get('pdf-invoice/{id}', [InvoiceController::class, 'pdfInvoice'])->name('invoice.pdf-invoice');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('invoice', [InvoiceController::class, 'datatable'])->name('invoice');
    Route::get('invoice-payment', [InvoicePaymentController::class, 'datatable'])->name('invoice-payment');
    Route::get('order-payment', [OrderPaymentController::class, 'datatable'])->name('order-payment');
    Route::get('invoice-order', [InvoiceController::class, 'datatableOrder'])->name('invoice-order');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('customer-invoice/{customerCode}', [InvoiceController::class, 'customerInvoice'])->name('customer-finance');
    Route::get('invoice-number-format/{id}', [InvoiceController::class, 'invoiceNumberFormat'])->name('invoice-number-format');
    Route::get('invoice/{id}/suggest-number', [InvoiceController::class, 'suggestInvoiceNumber'])->name('invoice.suggest-number');
    Route::get('order-detail-payment/{orderCode}', [OrderPaymentController::class, 'orderDetailPayment'])->name('order-detail-payment');
});
