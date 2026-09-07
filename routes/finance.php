<?php

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\VendorInvoiceController;
use Illuminate\Support\Facades\Route;
Route::prefix('finance')->name('finance.')->group(function () {
    // Vendor Payment kini berada di menu Vendor (routes/vendor.php).
    // URL lama dialihkan ke halaman baru agar bookmark tetap jalan.
    Route::get('vendor-payment', fn () => redirect()->route('vendor.invoice.unpaid'));

    // Order Payment kini menjadi menu tersendiri: Pembayaran Langsung (routes/direct-payment.php).
    // URL lama dialihkan ke halaman baru agar bookmark tetap jalan.
    Route::get('order-payment', fn () => redirect()->route('direct-payment.index'));
    Route::get('order-payment/{id}', fn ($id) => redirect()->route('direct-payment.show', $id));

    Route::get('pdf-vendor-payment/{orderCode}', [VendorInvoiceController::class, 'pdf'])->name('vendor-payment.pdf');

    // Fallback redirect dari route lama finance/invoice ke route baru invoice
    Route::get('invoice', fn () => redirect()->route('invoice.unpaid'));
    Route::get('invoice/create', fn () => redirect()->route('invoice.create'));
    Route::get('invoice-payment', fn () => redirect()->route('invoice.payment.index'));
    Route::get('pdf-invoice/{id}', [InvoiceController::class, 'pdfInvoice'])->name('invoice.pdf-invoice');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('invoice', [InvoiceController::class, 'datatable'])->name('invoice');
    Route::get('invoice-payment', [InvoicePaymentController::class, 'datatable'])->name('invoice-payment');
    Route::get('invoice-order', [InvoiceController::class, 'datatableOrder'])->name('invoice-order');
});

Route::prefix('ajax')->name('ajax.')->group(function () {
    Route::get('customer-invoice/{customerCode}', [InvoiceController::class, 'customerInvoice'])->name('customer-finance');
    Route::get('invoice-number-format/{id}', [InvoiceController::class, 'invoiceNumberFormat'])->name('invoice-number-format');
    Route::get('invoice/{id}/suggest-number', [InvoiceController::class, 'suggestInvoiceNumber'])->name('invoice.suggest-number');
});
